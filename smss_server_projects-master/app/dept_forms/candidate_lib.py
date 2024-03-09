#!/var/www/mthsc/common/venv/bin/python3

import os
import sys
import MySQLdb

sys.path.append("/var/www/mthsc/common") #delete/comment out when running on apache server
from common_lib import commonFunctions

class candidate_lib(commonFunctions):

    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary for the syllabus_rep database
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)

    #=============================================
    # accepts: none
    # returns: int id of current pool
    #
    def get_current_pool_id(self):
        cursor = self.get_cursor()

        sql = """SELECT value FROM candidate_settings WHERE name = "current_pool" """

        cursor.execute(sql)

        record = cursor.fetchone()

        if len(record) == 0:
            return 0
        else:
            return int(record["value"])

    #=============================================
    # accepts: int pool id
    # returns: True if the current pool id was set, and False otherwise
    #
    def set_current_pool(self,pool_id):
        cursor = self.get_cursor()

        cursor.execute("""UPDATE candidate_settings SET value = {} WHERE name = "current_pool" """.format(int(pool_id)))

        return True # we should really be checking to see if the UPDATE succeeded

    #=============================================
    # accepts: none
    # returns: list of pools
    #
    def get_pool_list(self):
        cursor = self.get_cursor()

        sql = "SELECT * FROM candidate_pool ORDER BY pool_id DESC"
        cursor.execute(sql)
        return cursor.fetchall()

    #=============================================
    # accepts: int person id
    # returns: list of evaluations
    #
    def get_evaluations_for_evaluator(self,person_id):
        cursor = self.get_cursor()

        sql = "SELECT * FROM candidate_list AS cl LEFT JOIN candidate_evaluators AS ce ON ce.candidate_id = cl.candidate_id AND ce.person_id = %s WHERE ISNULL(person_id) AND open_date <= NOW() AND close_date >= NOW()"
        cursor.execute(sql, (person_id,))
        return cursor.fetchall()

    #=============================================
    # accepts: int candidate id
    #         int form id
    # returns: form questions with stats
    #
    def get_stats(self,candidate_id, form_id):
        cursor = self.get_cursor()

        sql = "SELECT q.question_id, q.question_statement, COUNT(qo.option_id) AS option_count, qo.option_id, qo.option_type, qo.option_statement, rd.option_comments FROM candidate_responses AS r LEFT JOIN candidate_response_data AS rd ON rd.response_id = r.response_id LEFT JOIN candidate_question_options AS qo ON qo.option_id = rd.option_id LEFT JOIN candidate_questions AS q ON q.question_id = qo.question_id WHERE r.form_id = %s AND r.candidate_id = %s AND NOT ISNULL(rd.response_id) GROUP BY q.question_id, qo.option_id, rd.option_comments ORDER BY q.question_order, qo.option_order, rd.option_comments"
        cursor.execute(sql, (form_id, candidate_id))

        options = cursor.fetchall()
        cur_question_id = 0
        cur_option_id = 0
        questions = []

        for option in options:
            if cur_question_id != option.get('question_id'):
                questions.append({"id": option.get('question_id'), "statement": option.get('question_statement'), "options": []})
                cur_question_id = option.get('question_id')

            if cur_option_id != option.get('option_id'):
                questions[-1].get('options').append({"id": option.get('option_id'), "count": option.get('option_count'), "type": option.get('option_type'), "statement": option.get('option_statement'), "comments": []})
                cur_option_id = option.get('option_id')
                if option.get('option_type') in ["multiple_free", "free"]:
                    # we are going to have to count these incrementally
                    questions[-1]['options'][-1]['count'] = 0

            if option.get('option_type') in ["multiple_free", "free"]:
                questions[-1]['options'][-1]['count'] += option.get('option_count')

            if len(option.get('option_comments')) > 0:
                questions[-1].get('options')[-1].get('comments').append(option.get('option_comments'))

        return questions

    #=============================================
    # accepts: int person id
    #          int candidate id
    # returns: true if this person can evaluate this candidate; false if not
    #
    def valid_evaluator(self,person_id, candidate_id, form_id):
        cursor = self.get_cursor()

        sql = "SELECT COUNT(candidate_id) AS valid FROM candidate_evaluators WHERE person_id = %s AND candidate_id = %s AND form_id = %s"
        cursor.execute(sql, (person_id, candidate_id, form_id))

        if cursor.fetchone().get('valid') == 0:
            return True
        else:
            return False

    #=============================================
    # accepts: int question id
    # returns: list of options for the question
    #
    def get_options(self,question_id):
        cursor = self.get_cursor()

        sql = "SELECT * FROM candidate_question_options WHERE question_id = %s ORDER BY option_order"
        cursor.execute(sql, (question_id,))

        return cursor.fetchall()

    #=============================================
    # accepts: int form id
    # returns: list of questions for the form
    #
    def get_form_data(self,form_id):
        cursor = self.get_cursor()

        sql = "SELECT * FROM candidate_questions WHERE form_id = %s ORDER BY question_order"
        cursor.execute(sql, (form_id,))

        questions = cursor.fetchall()

        for question in questions:
            question["options"] = self.get_options(question.get('question_id'))

        return questions

    #=============================================
    # accepts: int candidate id
    #       int form id
    # returns: new response id
    #
    def create_response(self,candidate_id, form_id):
        cursor = self.get_cursor()

        sql = "INSERT INTO candidate_responses (candidate_id, form_id) VALUES (%s, %s)"
        cursor.execute(sql, (candidate_id, form_id))

        cursor.execute("SELECT LAST_INSERT_ID() AS id")

        return cursor.fetchone().get('id')

    #=============================================
    # accepts: int response id
    #          int form id
    #          str comment
    # returns: 1 if the response was stored, 0 if not
    #
    def store_multiple_free_response(self,response_id, option_id, option_comment):
        cursor = self.get_cursor()

        sql = "INSERT INTO candidate_response_data (response_id, option_id, option_comments) VALUES (%s, %s, %s)"
        cursor.execute(sql, (response_id, option_id, option_comment))

        return 1 # we really should be checking to see if the INSERT succeeded

    #=============================================
    # accepts: int response id
    #          int question id
    #          str comment
    # returns: 1 if the response was stored, 0 if not
    #
    def store_free_response(self,response_id, question_id, option_comment):
        cursor = self.get_cursor()

        sql = "SELECT option_id FROM candidate_question_options WHERE question_id = %s"
        cursor.execute(sql, (question_id,))

        # there should be only one option for a free response question
        if cursor.rowcount == 1:
            option_id = cursor.fetchone().get('option_id')

            return store_multiple_free_response(response_id, option_id, option_comment)
        else:
            return 0

    #=============================================
    # accepts: int person id
    #          int form id
    #          int candidate id
    # returns: 1 if the evaluation was saved; 0 if not
    #
    def mark_candidate_as_evaluated(self,person_id, form_id, candidate_id):
        cursor = self.get_cursor()

        sql = "INSERT INTO candidate_evaluators (person_id, form_id, candidate_id) VALUES (%s, %s, %s)"
        cursor.execute(sql, (person_id, form_id, candidate_id))

        return 1 # we really should be checking to see if the INSERT succeeded

    #=============================================
    # accepts: int pool id
    #          str first name
    #          str last name
    #          str school
    #          str visit_dates
    #          str open_date
    #          str close_date
    # returns: 1 if the candidate was saved; 0 if not
    #
    def add_candidate(self,pool_id, first_name, last_name, school, visit_dates, open_date, close_date):
        cursor = self.get_cursor()

        # note that we escape the % signs in the MYSQL date formatting
        sql = """INSERT INTO candidate_list (pool_id, first_name, last_name, school, visit_dates, open_date, close_date) VALUES (%s, %s, %s, %s, %s, STR_TO_DATE(%s, "%%m/%%e/%%y"), STR_TO_DATE(%s, "%%m/%%e/%%y"))"""
        cursor.execute(sql, (pool_id, first_name, last_name, school, visit_dates, open_date, close_date))

        return 1 # we really should be checking to see if the INSERT succeeded

    #=============================================
    # accepts: int candidate id
    #          int pool id
    #          str first name
    #          str last name
    #          str school
    #          str visit_dates
    #          str open_date
    #          str close_date
    # returns: 1 if the candidate was saved; 0 if not
    #
    def update_candidate(self,candidate_id, pool_id, first_name, last_name, school, visit_dates, open_date, close_date):
        cursor = self.get_cursor()

        # note that we escape the % signs in the MYSQL date formatting
        sql = """UPDATE candidate_list SET pool_id = %s, first_name = %s, last_name = %s, school = %s, visit_dates = %s, open_date = STR_TO_DATE(%s, "%%m/%%e/%%y"), close_date = STR_TO_DATE(%s, "%%m/%%e/%%y") WHERE candidate_id = %s"""
        cursor.execute(sql, (pool_id, first_name, last_name, school, visit_dates, open_date, close_date, candidate_id))

        return 1 # we really should be checking to see if the UPDATE succeeded

    #=============================================
    # accepts: int candidate id
    # returns: 1 if the candidate was deleted; 0 if not
    #
    def delete_candidate(self,candidate_id):
        cursor = self.get_cursor()

        sql = """SELECT COUNT(candidate_id) AS evaluation_count FROM candidate_evaluators WHERE candidate_id = %s"""
        cursor.execute(sql, (candidate_id,))

        if cursor.fetchone().get('evaluation_count') == 0:
            sql = """DELETE FROM candidate_list WHERE candidate_id = %s"""
            cursor.execute(sql, (candidate_id,))

            return True # we really should be checking to see if the DELETE succeeded
        else:
            return False

    #=============================================
    # accepts: int pool id
    # returns: list of candidates
    #
    def get_candidate_list(self,pool_id):
        cursor = self.get_cursor()

        sql = "SELECT * FROM candidate_list WHERE pool_id = %s ORDER BY open_date, last_name, first_name"
        cursor.execute(sql, (pool_id,))

        return cursor.fetchall()

    #=============================================
    # accepts: int candidate id
    # returns: info about candidate
    #
    def get_candidate_info(self,candidate_id):
        cursor = self.get_cursor()

        sql = "SELECT * FROM candidate_list WHERE candidate_id = %s"
        cursor.execute(sql, (candidate_id,))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts: int pool id
    # returns: info about pool
    #
    def get_pool_info(self,pool_id):
        cursor = self.get_cursor()

        sql = "SELECT * FROM candidate_pool WHERE pool_id = %s"
        cursor.execute(sql, (pool_id,))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {}

    #=============================================
    # accepts:  str pool description
    # returns: 1 if the pool was saved; 0 if not
    #
    def add_pool(self,pool_description):
        cursor = self.get_cursor()

        sql = """INSERT INTO candidate_pool (description) VALUES (%s)"""
        cursor.execute(sql, (pool_description,))

        return 1 # we really should be checking to see if the INSERT succeeded

    #=============================================
    # accepts:  int pool id
    #               str pool description
    # returns: 1 if the pool was saved; 0 if not
    #
    def update_pool(self,pool_id, pool_description):
        cursor = self.get_cursor()

        sql = """UPDATE candidate_pool SET description = %s WHERE pool_id = %s"""

        cursor.execute(sql, (pool_description, pool_id))

        return 1 # we really should be checking to see if the UPDATE succeeded

    #=============================================
    # accepts: int pool id
    # returns: 1 if the pool was deleted; 0 if not
    #
    def delete_pool(self,pool_id):
        cursor = self.get_cursor()

        sql = """SELECT COUNT(candidate_id) AS candidate_count FROM candidate_list WHERE pool_id = %s"""
        cursor.execute(sql, (pool_id,))

        if cursor.fetchone().get('candidate_count') == 0:
            sql = """DELETE FROM candidate_pool WHERE pool_id = %s"""
            cursor.execute(sql, (pool_id,))

            return True # we really should be checking to see if the DELETE succeeded
        else:
            return False

    #=============================================
    # accepts: int pool id
    # returns: html for candidate_list
    #
    def get_cand_list_html(self,pool_id):
        cand_list_html = ""

        cand_list = self.get_candidate_list(pool_id)

        current_pool = self.get_pool_info(pool_id)

        if len(current_pool) > 0:
            pool_description = current_pool.get('description')
        else:
            pool_description = "Current pool not set!"

        if len(cand_list) > 0:
            cand_list_html = "\n\t".join([f"""
<div class="candidate">
    <div style="float: right;">
        <ul style="list-style: none; margin-top: 0px; padding-top: 0px;">
        <li><form action="stats" method="POST">
          <input type="hidden" name="candidate_id" value="{item.get('candidate_id')}">
          <input type="Submit" value="View Stats">
        </form></li>
        <li><form action="manage_candidate" method="POST">
          <input type="hidden" name="candidate_id" value="{item.get('candidate_id')}">
          <input type="hidden" name="action" value="edit">
          <input type="Submit" value="Edit Candidate">
        </form></li>
        <li><form action="manage_candidate" method="POST">
          <input type="hidden" name="candidate_id" value="{item.get('candidate_id')}">
          <input type="hidden" name="action" value="delete">
          <input type="Submit" value="Delete Candidate">
        </form></li>
        </ul>
        </div>
        <div class="name">{item.get('last_name')}, {item.get('first_name')}</div>
        <div>{item.get('school')}</div>
        <div><span class="label">Visit Dates</span>: {item.get('visit_dates')}</div>
        <div><span class="label">Evaluation Dates</span>: {item.get('open_date').strftime("%m/%d/%y").lstrip("0")} - {item.get('close_date').strftime("%m/%d/%y").lstrip("0")}</div>
</div>""" for item in cand_list])
        else:
            cand_list_html = "There are no candidates at this time."

        return pool_description, cand_list_html


    #=============================================
    # accepts: none
    # returns: html for pool dropdown list
    #
    def get_pool_dropdown_html(self,pool_id):
        pool_ids = []
        pool_descriptions = []

        for pool in self.get_pool_list():
            pool_ids.append(pool.get('pool_id'))
            pool_descriptions.append(pool.get('description'))

        return self.get_dropdown_html(pool_ids, pool_descriptions, pool_id, "pool_id")

    #=============================================
    # accepts: list of dicts
    # returns: html for candidate stats
    #
    def generate_stat_form(self,questions):
        stats_html = ""
        for question in questions:
            option_html = ""
            for option in question.get('options'):
                if option["type"] == "multiple":
                    option_html += f"""    <li>{option.get('statement')} ({option.get('count')})</li>\n"""
                elif option["type"] == "multiple_free":
                    comment_html = "\n".join(["""<div class="comment_box">%s</div>""" % comment for comment in option["comments"]])
                    option_html += f"""    <li>{option.get('statement')} ({option.get('count')})
    {comment_html}
    </li>\n"""
                elif option["type"] == "free":
                    comment_html = "\n".join(["""<div class="comment_box">%s</div>""" % comment for comment in option["comments"]])
                    option_html += f"""    <li>{comment_html}</li>\n"""

            option_html = f"""
    <ul class="option_list">
    {option_html}
    </ul>
    """

            stats_html += f"""
    <div class="question">
    <div class="question_statement">{question.get('statement')}</div>
    <div>
    {option_html}
    </div>
    </div>
    """

        return stats_html
