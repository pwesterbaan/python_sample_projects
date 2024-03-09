#!/var/www/mthsc/common/venv/bin/python3

import os
import cgi
import cgi_lib as cl
import survey_lib as sl


def csv_escape(list):
    # replace " with ""
    # wrap each column in double quotes
    line = ",".join(["\"%s\"" % str(item).replace("\"", "\"\"") for item in list])
    return line


data = sl.get_survey_data()

print("MIME-Version: 1.0\r")
print("Content-Disposition: attachment; filename=bridge_course_survey_data.csv;\r")
print("Content-Type: text/csv\r\n\r")

if(len(data) > 0):
    # print header row
    print(csv_escape(data[0].keys()))

    # print all data
    print("\n".join([csv_escape(row.values()) for row in data]))
