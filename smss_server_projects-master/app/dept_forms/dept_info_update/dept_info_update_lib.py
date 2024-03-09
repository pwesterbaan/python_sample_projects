#!/var/www/mthsc/common/venv/bin/python3

import sys

sys.path.append('/var/www/mthsc/html/app/dept_forms')
from dept_forms_lib import dept_forms_lib

class dept_info_update_lib(dept_forms_lib):

    #=============================================
    # input:    str name
    #           str title
    #           str instructions
    #           str data
    #           str width
    #           str height
    # output:   str html for data display
    def data_box(self, name, title, instructions, data, width, height):
        if len(instructions) > 0:
            instructions = f"""
            <div style="margin-left: 30px;">{instructions}</div>
"""

        return f"""
            <div id="{name}_title">{title}</div>{instructions}
            <textarea id="{name}" onChange="javascript:update_info('{name}');" class="text_data" style="width:{width}spx; height: {height}px;">{datan}</textarea>
"""


    #=============================================
    # accepts: array values
    #          array display_text
    #          type_of(value elements) selected_index
    #          string name
    #          string params (i.e. style, onchange, onclick, id, etc.)
    # returns: the html for a drop down list
    #
    def get_diu_dropdown_html(self,values, display_text, selected_index, name, params = None):
        #build string based off of params and pass as "extras" from inherited function
        if params is None:
            params = {"name": name}
        else:
            params["name"] = name
            
        param_str = " ".join([f"""{key}=\"{value}\"""" for key, value in params.items()])
        if len(param_str) > 0:
            param_str = " " + param_str

        output = self.get_dropdown_html(values, display_text, selected_index, name, params_str)

        return output
    

    #=============================================
    # accepts: string field of update
    #          string item_id
    #          form dict
    # returns: the text representing a description appropriate for this field
    #
    def get_update_description(self,field, item_id, form):
        if field == "employee_id":
            return form.get(item_id,"")
        elif field == "position":
            return form.get(f'''{item_id}_description''',"")
        elif field == "student":
            return form.get('''{item_id}_name''',"")

        elif field == "cuid":
            return form.get(item_id,"")
        elif field == "advisor":
            name = form.get(f'''{item_id}_name''',"")
            advisor_type = form.get(f'''{item_id}_type''',"")
        
            return f"""{name} [{advisor_type}]""" 
        elif field == "degree":
            program = form.get(f'''{item_id}_program''',"")
            current_degree = form.get(f'''{item_id}_current''',"")
            area = form.get(f'''{item_id}_area''',"")
            start_semester = form.get(f'''{item_id}_start_semester''',"")
            start_year = form.get(f'''{item_id}_start_year''',"")
            end_semester = form.get(f'''{item_id}_end_semester''',"")
            end_year = form.get(f'''{item_id}_end_year''',"")
        
            return f"""Degree Program: {program}
Current Degree: {current_degree}
Area: {area}
Start: {semester_start} {start_year}
End: {end_semester} {end_year}"""

        elif field == "benchmark":
            attempt_date = form.get(f'''{item_id}_datetime''',"")
            benchmark_type = form.get(f'''{item_id}_type''',"")
            pass_str = form.get(f'''{item_id}_passed''',"")
        
            return "%s - %s [%s]" % (attempt_date, benchmark_type, pass_str)
        elif field == "gre":
            verbal = form.get(f'''{item_id}_verbal''',"")
            quantitative = form.get(f'''{item_id}_quantitative''',"")
            writing = form.get(f'''{item_id}_writing''',"")
        
            return """Verbal: %s
Quantitative: %s
Writing: %s""" % (verbal, quantitative, writing)
        elif field == "toefl":
            return form.get(item_id)
        elif field == "speak_test":
            attempt_date = form.get(f'''{item_id}_datetime''',"")
            pass_str = form.get(f'''{item_id}_passed''',"")
        
            return "%s - %s" % (attempt_date, pass_str)

        elif field == "username":
            return form.get(item_id)
        elif field == "first_name":
            return form.get(item_id)
        elif field == "pref_name":
            return form.get(item_id)
        elif field == "display_name":
            return form.get(item_id)
        elif field == "middle_name":
            return form.get(item_id)
        elif field == "last_name":
            return form.get(item_id)
        elif field == "maiden_name":
            return form.get(item_id)
        elif field == "sex":
            return form.get(item_id)
        elif field == "email_address":
            value = form.get(f'''{item_id}_value''',"")
            email_addy_type = form.get(f'''{item_id}_type''',"")
        
            return f"""{value} [{email_addy_type}]"""
        elif field == "mail_address":
            value = form.get(f'''{item_id}_value''',"")
            mail_addy_type = form.get(f'''{item_id}_type''',"")

            return f"""[{type}]\n{value}"""
        elif field == "office":
            return form.get(f'''{item_id}_description''',"")
        elif field == "phone_number":
            value = form.get(f'''{item_id}_value''',"")
            phone_num_type = form.get(f'''{item_id}_type''',"")
        
            return f"""{value} [{phone_num_type}]"""
        elif field == "education":
            degree = form.get(f'''{item_id}_degree''',"")
            major = form.get(f'''{item_id}_major''',"")
            school = form.get(f'''{item_id}_school_name''',"")
            semester = form.get(f'''{item_id}_semester''',"")
            year = form.get(f'''{item_id}_year''',"")
            gpa = form.get(f'''{item_id}_gpa''',"")
        
            return f"""{degree} in {major} from {school}, {semester} {year} ({gpa})"""
        elif field == "visa":
            return form.get(item_id)

        else:
            return "unknown field type: " + field


        #=============================================
        # accepts:  string field to check for updates
        #           string field_display (text to print for this field in the email)
        #           list additions
        #           list deletions
        #           list changes
        #           form dict
        # returns: the text representing these updates
        #
        def get_updates(field, field_display, additions, deletions, changes, form):
            update_text = ""
            
            if field in deletions.keys():
                if len(deletions[field]) > 0:
                    update_text += "Deletions:\n\n"

                    counter = 1
                    for item_id in deletions[field]:
                        # item_id is really the item description for deleted items
                        update_text += f"""{str(counter)}. {item_id}\n\n"""
                        counter += 1
                
                    update_text += "\n"
            
            if field in changes.keys():
                if len(changes[field]) > 0:
                    update_text += "Changes:\n\n"

                    counter = 1
                    for item_id in changes[field]:
                        update_text += str(counter) + ".) \n"
                        update_text += f"""Old\n----\n{form.get(f'''orig_{item_id}''',"")}\n\n"""
                        update_text += f"""New\n----\n{self.get_update_description(field, item_id, form)}\n\n"""
                        counter += 1
                
                    update_text += "\n"
            
            if field in additions.keys():
                if len(additions.get(field)) > 0:
                    update_text += "Additions:\n\n"
            
                    counter = 1
                    for item_id in additions[field]:
                        update_text += f"""{str(counter)}. {self.get_update_description(field, item_id, form)}\n\n"""
                        counter += 1
                
                    update_text += "\n"

            if len(update_text) > 0:
                update_text = f"""{field_display}
===========================
{update_text}
"""

            return update_text
