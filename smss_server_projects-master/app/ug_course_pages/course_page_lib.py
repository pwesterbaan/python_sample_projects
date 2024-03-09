#!/var/www/mthsc/common/venv/bin/python3

import os
import random
import string
import sys
import MySQLdb

from email.mime import text as MIMEText

sys.path.append("/var/www/mthsc/common") #delete/comment out when running on apache server
from common_lib import commonFunctions

class course_page_lib(commonFunctions):

    #=============================================
    # accepts: none
    # returns: mysqldb cursor dictionary
    #
    def get_cursor(self):
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db = MySQLdb.connect(host="localhost", user="user", passwd="passwd", db="db_name")
        db.autocommit(True)

        return db.cursor(MySQLdb.cursors.DictCursor)

    #=============================================
    # accepts: nothing
    #
    # returns: string the current semester
    #
    def get_current_semester(self):
        cursor = self.get_cursor()

        sql = """SELECT value FROM ug_page_settings WHERE name = "semester" """

        cursor.execute(sql)

        record = cursor.fetchone()

        if len(record) == 0:
            return "spring"
        else:
            return record["value"]

    #=============================================
    # accepts: nothing
    # returns: string the current semester
    #
    def get_current_year(self):
        cursor = self.get_cursor()

        sql = """SELECT value FROM ug_page_settings WHERE name = "year" """

        cursor.execute(sql)

        record = cursor.fetchone()

        if len(record) == 0:
            now = datetime.datetime.now()
            return str(now.year)
        else:
            return record["value"]

    #=============================================
    # accepts: int course id
    # returns: str course title
    #
    def get_course_title(self,course_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT prefix, course_num FROM course.course_list WHERE course_id = '{}'".format(course_id))

        if cursor.rowcount == 1:
            data = cursor.fetchone()
            return f"""{data.get('prefix')} {data.get('course_num')}"""
        else:
            return ""


    #=============================================
    # accepts: int course id
    # returns: str course description
    #
    def get_course_description(self,course_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT description FROM course.course_list WHERE course_id = '{}'".format(course_id))

        if cursor.rowcount == 1:
            return cursor.fetchone()["description"]
        else:
            return ""

    #=============================================
    # accepts: int course id
    # returns: str username of coordinator
    #
    def get_course_coord_username(self,course_id):
        cursor = self.get_cursor()

        cursor.execute("SELECT employee_username FROM course.course_coordinators WHERE course_id = '{}' AND semester = '{}' AND year = '{}'".format(course_id, self.get_current_semester(), self.get_current_year()))

        if cursor.rowcount > 0:
            return cursor.fetchone()["employee_username"].lower()
        else:
            return ""

    #=============================================
    # accepts: nothing
    # returns: a list of the categories in order in the format {id, title}
    #
    def get_categories(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM categories ORDER BY view_order")

        if cursor.rowcount > 0:
            return cursor.fetchall()
        else:
            return []

    #=============================================
    # accepts: int course id
    # returns: 1 if the update time was saved, 0 if not
    #
    def set_last_update(self,course_id):
        cursor = self.get_cursor()
    
        cursor.execute("""INSERT INTO updates (course_id, last_updated) VALUES ('{}', CURDATE()) ON DUPLICATE KEY UPDATE last_updated = CURDATE()""".format(course_id))
    
        return 1 # we should be checking to see if the INSERT succeeded

    #=============================================
    # accepts: int course id
    # returns: a dictionary containing lists of items in the users profile
    #
    def get_course_content(self,course_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT c.category_description,
 c.category_id,
 pc.content_id,
 pc.content,
 IF(pc.content IS NULL,1,0) AS empty_category
 FROM categories AS c
 LEFT JOIN page_content AS pc
 ON pc.category_id = c.category_id
 WHERE course_id = '{}' OR
 course_id IS NULL
 ORDER BY
 c.view_order,
 pc.order,
 pc.content_id""".format(str(course_id)))

        # get the resultset as a dictionary
        result = cursor.fetchall()

        output_dict = {}

        for record in result:
            cur_key = f"""{record.get('category_id')};{record.get('category_description')}"""
            if cur_key in output_dict:
                output_dict[cur_key].append([record.get('content_id'), record.get('content')])
            else:
                if record["empty_category"] == 1:
                    output_dict[cur_key] = []
                else:
                    output_dict[cur_key] = []
                    output_dict[cur_key].append([record.get('content_id'), record.get('content')])

        return output_dict

    #=============================================
    # accepts: int course id
    # returns: a list of uploaded items for the class
    #
    def get_course_items(self,course_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM uploaded_items WHERE course_id = '{}' ORDER by description""".format(course_id))

        return cursor.fetchall()

    #=============================================
    # accepts: int category id
    #          int course id
    #          str content 
    # returns: 1 if the item was added, 0 if not
    #
    def add_course_content(self,category_id, course_id, content):
        cursor = self.get_cursor()

        cursor.execute("""INSERT INTO page_content (category_id, course_id, content, `order`) VALUES ('{}', '{}', '{}', 0)""".format(int(category_id), int(course_id), content))

        return 1 # we should be checking to see if the INSERT succeeded

    #=============================================
    # accepts: int content id 
    # returns: 1 if the item was deleted, 0 if not
    #
    def delete_course_content(self,content_id):
        cursor = self.get_cursor()

        cursor.execute("""DELETE FROM page_content WHERE content_id = '{}'""".format(content_id))

        return 1 # we should be checking to see if the DELETE succeeded

    #=============================================
    # accepts: int course id
    #          int content id
    #          str content text
    # returns: 1 if the item was updated, 0 if not
    #
    def update_course_content(self,course_id, content_id, content_text):
        cursor = self.get_cursor()
    
        cursor.execute("""UPDATE page_content SET content = '{}' WHERE course_id = '{}' AND content_id = '{}'""".format(content_text, course_id, content_id))

        return 1 # we should be checking to see if the UPDATE succeeded

    #=============================================
    # accepts: int course id
    # returns: string representing the date
    #
    def get_last_course_update(self,course_id):
        cursor = self.get_cursor()
    
        cursor.execute("""SELECT DATE_FORMAT(last_updated, "%c/%e/%y") AS last_updated FROM updates WHERE course_id = '{}'""".format(course_id))
    
        if cursor.rowcount == 1:
            return cursor.fetchone()["last_updated"] # we should be checking to see if the SELECT succeeded
        else:
            return "never"

    #=============================================
    # accepts: int course id
    # returns: 1 if the update time was saved, 0 if not
    #
    def set_last_course_update(self,course_id):
        cursor = self.get_cursor()
    
        cursor.execute("""INSERT INTO updates (course_id, last_updated) VALUES ('{}', NOW()) ON DUPLICATE KEY UPDATE last_updated = NOW()""".format(course_id))
    
        return 1 # we should be checking to see if the UPDATE succeeded

    #=============================================
    # accepts: nothing
    # returns: list of course with course pages
    #
    def get_page_list(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM page_list AS pl LEFT JOIN course.course_list AS cl ON cl.course_id = pl.course_id ORDER BY prefix, course_num")

        return cursor.fetchall()

    #=============================================
    # accepts: int course id
    #          str simple_filename
    #          str description
    # returns: str new_filename (local file name)
    #
    def add_new_uploaded_item(self,course_id, simple_filename, description):
        pieces = simple_filename.split(".")

        if len(pieces) > 1:
            file_type = pieces[-1]
        else:
            file_type = ""

        #generate a unique, random filename
        new_filename=self.generate_new_filename("uploaded_items","filepath",file_type)

        #store local filename associated with this file in database
        cursor = self.get_cursor()
        cursor.execute("""INSERT INTO uploaded_items (course_id, download_filename, filepath, description) VALUES ('{}', '{}', '{}', '{}')""".format(course_id, simple_filename, new_filename, description))

        #return new_filename to save file
        return new_filename


    #=============================================
    # accepts: int item id
    # returns: 1 if the item was deleted, 0 if not
    #
    def delete_uploaded_item(self,item_id,upload_folder):
        cursor = self.get_cursor()

        # delete the file from the disk
        cursor.execute("""SELECT filepath FROM uploaded_items WHERE item_id = '{}'""".format(item_id))

        if cursor.rowcount == 1:
            # we only proceed if there actually was an item with this id
            record = cursor.fetchone()

            del_path = os.path.join(upload_folder,os.path.basename(record["filepath"]))

            if os.path.exists(del_path):
                os.remove(del_path)

        cursor.execute("""DELETE FROM uploaded_items WHERE item_id = '{}'""".format(item_id))

        return 1 # we really should be checking to see if the DELETE succeeded


    #=============================================
    # accepts: int item id
    # returns: dict of info about the item
    #
    def get_uploaded_item_data(self,item_id):
        cursor = self.get_cursor()

        cursor.execute("""SELECT * FROM uploaded_items WHERE item_id = '{}'""".format(item_id))

        if cursor.rowcount == 1:
            return cursor.fetchone()
        else:
            return {}


    #=============================================
    # accepts: str employee username
    # returns: list of courses this user can edit
    #
    def get_course_list(self,employee_username):
        cursor = self.get_cursor()

        cursor.execute("""SELECT cl.course_id, cl.prefix, cl.course_num FROM editors AS e LEFT JOIN course.course_list AS cl ON cl.course_id = e.course_id WHERE employee_username = '{}' ORDER BY cl.prefix, cl.course_num""".format(employee_username.upper()))

        return cursor.fetchall()

    #=============================================
    # accepts: int course_id
    # returns: html to generate view_course_page/<course_id>
    #
    def get_html_view_course_page(self,course_id):
        content_html=""

        content_dict = self.get_course_content(course_id)

        categories = self.get_categories()

        for cat in categories:
            cat_id = cat["category_id"]
            cat_title = cat["category_description"]

            cat_key = f"{cat_id};{cat_title}"

            if (cat_key in content_dict.keys()) and (len(content_dict[cat_key]) > 0):
                content_html += f"""
        <div class="course_page_heading">
                {cat_title}
        </div>"""

                for item in content_dict[cat_key]:
                    item_id = item[0]
                    content_html += f"""
        <div class="course_page_field">{item[1]}</div>"""

        content_html += "\n"

        return content_html

    #=============================================
    # accepts: int course_id
    # returns: html for edit_course_page/<course_id>
    #
    def get_html_edit_course_page(self,course_id):
        content_html = ""

        content_dict = self.get_course_content(course_id)

        categories = self.get_categories()

        for cat in categories:
            cat_id = cat.get('category_id')
            cat_title = cat.get('category_description')

            cat_key = f"{cat_id};{cat_title}"

            content_html += f"""
<div id="cat_{cat_id}" style="padding-bottom: 7px; padding-top: 7px;">
        <div>
                <span style="font-weight: bold; font-size: 20px;">{cat_title}</span>
                <div style="height: 13px; margin-left: 20px;">
                        <a href="javascript:add_content('cat_{cat_id}');" style="cursor: pointer;">
                                <img title="Add a new item" class="svg_green_plus" alt="add item">
                                <span style="color: #646660; font-size: 13px;">Add a new item</span>
                        </a>
                </div>
        </div>"""

            if (cat_key in content_dict.keys()) and (len(content_dict[cat_key]) > 0):
                for content in content_dict.get(cat_key):
                    content_id = content[0]
                    content_html += f"""
<div style="margin: 20px;">
        <label for="old_{content_id}" style="display:none;">Item {content_id}</label>
        <textarea id="old_{content_id}" class="course_page_editor" name="old_{content_id}">{content[1]}</textarea>
</div>"""

            content_html += "\n</div>"

        return content_html

    #=============================================
    # accepts: int course_id
    # returns: html for manage_content/<course_id>
    #
    def get_html_manage_content(self,course_id):
        uploaded_items = self.get_course_items(course_id)

        list_html = ""

        for item in uploaded_items:
            list_html += f"""
<tr>
    <td>{item.get('description')}</td>
    <td><a href="view_item.php?id={item.get('item_id')}">{item.get('download_filename')}</a></td>
    <td>
        <form action="/ug_course_pages/manage_content/{course_id}" method="POST">
            <input type="submit" id="delete-button" value="Delete this item">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="course_id" value="{course_id}">
            <input type="hidden" name="item_id" value="{item.get('item_id')}">
        </form>
    </td>
</tr>"""

        content_html = f"""
<table>
    <tr><th>Description</th>
    <th>Filename</th>
    <th></th></tr>
{list_html}
</table>
"""

        return content_html
