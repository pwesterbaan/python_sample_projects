#!/var/www/mthsc/common/venv/bin/python3

# common_lib.py
# contains code that is common to mthsc server projects

from datetime import datetime

import cx_Oracle

class commonFunctions:
    #=============================================
    # Database related methods                   #
    #=============================================
    # accepts: none
    # returns: cx_Oracle cursor dictionary
    #
    def get_banner_cursor(self):
        dsn = cx_Oracle.makedsn("db_name", port, service_name = "service_name")
        con = cx_Oracle.connect(user = user, password = password, dsn = dsn)

        banner_cursor = con.cursor()
        return banner_cursor

    #=============================================
    # accepts: str username
    # returns: True if this user is an admin; False otherwise
    #
    def is_admin(self,username):
        cursor = self.get_cursor()

        cursor.execute("SELECT COUNT(username) AS valid FROM users WHERE username = '{}' AND role = 'admin'".format(username))

        if cursor.fetchone()["valid"] == 1:
            return True
        else:
            return False

    #=============================================
    # accepts: str username
    # returns: True if this user is a staff; False otherwise
    #
    def is_staff(self,username):
        cursor = self.get_cursor()

        cursor.execute("SELECT COUNT(username) AS valid FROM users WHERE username = '{}' AND role = 'staff'".format(username))
        if cursor.fetchone()["valid"] == 1:
            return True
        else:
            return False

    #============================================
    # accepts: none
    # returns: list of admins/staff
    #
    def get_access_list(self):
        cursor = self.get_cursor()

        cursor.execute("SELECT * FROM users ORDER BY role,username")
        return cursor.fetchall()

    #============================================
    # accepts: str username
    #          str role
    # returns: true
    #
    def add_access(self,username,role):
        cursor = self.get_cursor()
        cursor.execute("INSERT INTO users (username, role) VALUES ('{}','{}')".format(username, role))
        return True

    #============================================
    # accepts: str username
    #          str role
    # returns: true
    #
    def revoke_access(self,username,role):
        cursor = self.get_cursor()
        cursor.execute("DELETE FROM users WHERE username = '{}' AND role='{}'".format(username, role))
        return True


    #=============================================
    # formatting related methods                 #
    #=============================================
    # accepts: array containing key value pairs from a form,
    #          string representing a key in the form
    # returns: the string representation of the value
    #          specified by key, if the key is not associated with
    #          a value then the default value is returned
    #          if a default value is not specified then an empty string is returned
    def get_str_value(self,form, key, *default):
        value=form.get(key)
        if value == None:
            if len(default) == 1:
                return default[0]
            else:
                return ""
        else:
            return value

    #=============================================
    # accepts: array containing key value pairs from a form,
    #          string representing a key in the form,
    #          optional default value to return if key is not found
    # returns: the integer representation of the value
    #          specified by key, if the key is not associated with
    #          a value then the default value is returned
    #          if a default value is not specified then 0 is returned
    def get_int_value(self,form_data, key, *default):
        temp = form_data.get(key)
        try :
            return int(float(temp))
        except :
            if len(default) == 1:
                return default[0]
            else:
                return 0

    #=============================================
    # accepts: array containing key value pairs from a form,
    #          string representing a key in the form,
    #          optional default value to return if key is not found
    # returns: the decimal representation of the value
    #          specified by key, if the key is not associated with
    #          a value then the default value is returned
    #          if a default value is not specified then 0 is returned
    ## PW 2021-12-23: This is an old function that is no longer used
    def get_float_value(self,form_data, key, *default):
        temp = form_data.get(key)
        try :
            return float(temp)
        except :
            if len(default) == 1:
                return default[0]
            else:
                return 0.0

    #=============================================
    # accepts: array containing key value pairs from a form,
    #          string representing a key in the form,
    # returns: the list of values specified by the key
    ## PW 2021-12-23: This is an old function that is no longer used
    def get_list_value(self, form, key):
        if form.has_key(key):
            temp = form.getvalue(key)
            if isinstance(temp, list):
                return temp
            else:
                return [temp]
        else:
            return []


    #=============================================
    # accepts: str semester,
    #          str year
    # returns: str termcode
    def get_term_code(self, semester, year):
        semester = semester.lower()

        if semester == "spring":
            semester = "01"
        elif semester == "summer i":
            semester = "05"
        elif semester == "summer ii":
            semester = "06"
        else: # it must be the fall
            semester = "08"

        return f'{year}{semester}'


    # PW 2022-05-20: Method used in the following files
    # /var/www/mthsc/html/
    # ./syllabus_repository/syllabus_rep_lib.py
    # ./dept_forms/dept_info_update/dept_info_lib_link.py
    # ./dept_forms/dept_info_lib.py
    # ./dept_info/dept_info_lib.py
    # ./assessment/bst/bst_lib.py
    # ./chair_candidate_evaluation/dept_info_lib.py
    # ./chair_candidate_feedback/dept_info_lib.py
    # ./course_manager/course_manager_lib.py
    # ./prereq/prereq_lib.py
    # ./grade_collection/grade_collection_lib.py
    # ./rolls/rolls_lib.py
    #=============================================
    # accepts: str year,
    #          str extras,
    #          int first_year
    # returns: the html for a drop down list of years from first_year till next year
    #
    def get_year_dropdown(self, selected, extras="", first_year=2013, menu_name="year"):
        now = datetime.now()
        years = range(now.year+1, first_year-1, -1)

        output = f"""
        <select name={menu_name} id={menu_name}{extras}>"""

        for year in years:
            select_flag = ""
            if year == int(selected):
                select_flag = " selected"
            output += f"""
        <option value="{year}"{select_flag}>{year}</option>"""

        output += """
    </select>
"""

        return output

    #=============================================
    # accepts: array values
    #          array display_text
    #          type_of(value elements) selected_index
    #          string name
    # returns: the html for a drop down list
    #
    def get_dropdown_html(self, values, display_text, selected_index, name):
        output = f"""<select name="{name}">"""

        for i in range (0, len(values)):
            select_str = ""
            if values[i] == selected_index:
                select_str = " selected"
            output += f"""
    <option value="{values[i]}"{select_str}>{display_text[i]}</option>"""

        output += """
</select>
"""

        return output

    # getters and setters #

    #=============================================
    # accepts: none
    # returns: str semester
    #
    def get_current_semester(self):
        return self.get_setting("current_semester")

    #=============================================
    # accepts: str semester
    # returns: True/False if the setting was saved
    #
    def set_current_semester(self,semester):
        return self.set_setting("current_semester", semester)

    #=============================================
    # accepts: none
    # returns: str term
    #
    def get_current_term(self):
        return self.get_setting("current_term")

    #=============================================
    # accepts: str term
    # returns: True/False if the setting was saved
    #
    def set_current_term(self,term):
        return self.set_setting("current_term", term)

    #=============================================
    # accepts: none
    # returns: str year
    #
    def get_current_year(self):
        return self.get_setting("current_year")

    #=============================================
    # accepts: str year
    # returns: True/False if the setting was saved
    #
    def set_current_year(self,year):
        return self.set_setting("current_year", year)

    #=============================================
    # accepts: str name
    # returns: str value
    #
    def get_setting(self,name):
        cursor = self.get_cursor()

        cursor.execute("SELECT value FROM settings WHERE name = '{}'".format(name))

        if cursor.rowcount == 1:
            return cursor.fetchone().get('value')
        else:
            return ""

    #=============================================
    # accepts: str name
    #          str value
    # returns: True/False if the setting was saved
    #
    def set_setting(self,name,value):
        cursor = self.get_cursor()

        cursor.execute("UPDATE settings SET value = '{}' WHERE name = '{}'".format(value, name))

        return self.get_setting(name)==str(value)

    #=============================================
    # accepts: str comma seperated values
    # returns: list of the values
    ## PW 2021-12-23: This is an old function that is no longer used
    def csv_to_list(self, data_str):
        data_str = data_str.strip()
        if len(data_str) > 0:
            return data_str.split(",")
        else:
            return []

    #=============================================
    # accepts: array containing key value pairs from a form,
    #          string representing a key in the form
    # returns: the string representation of the value
    #          specified by key, if the key is not associated with
    #          a value then the default value is returned
    #          if a default value is not specified then an empty string is returned
    ## PW 2021-12-23: This is an old function that is no longer used
    def get_date_value(self, form, key, *default):
        if key in form.keys():
            str_value = self.get_str_value(form, key)
            return datetime.strptime(str_value, "%m-%d-%Y")
        else:
            if len(default) == 1:
                return default[0]
            else:
                return ""
