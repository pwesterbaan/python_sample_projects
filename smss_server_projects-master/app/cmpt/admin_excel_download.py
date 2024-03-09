#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io
import pandas as pd
import time
import xlwt

from flask import Flask, request, Blueprint, send_file

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/<any(admin,engr):access_type>_excel_download',methods=['POST'])
@htpasswd.required
def admin_excel_download(user,access_type):
    form             = request.form
    cohort_selection = form.get('cohort','none')
    score_type       = form.get('score_type','all')

    xl_buffer = io.BytesIO()
    writer = pd.ExcelWriter(xl_buffer,engine='xlsxwriter')

    wb = xlwt.Workbook()
    ws = wb.add_sheet("scores")

    if score_type == "best":
        data = cmpt.get_best_scores(cohort_selection)
    else:
        data = cmpt.get_all_scores(cohort_selection)

    identifier_col = 'MthSc ID' if access_type == 'admin' else 'Name'
    bold_style = xlwt.easyxf("font: bold on")
    ws.write(0, 0, "XID", bold_style)
    ws.write(0, 1, "Username", bold_style)
    ws.write(0, 2, identifier_col, bold_style)
    ws.write(0, 3, "Score", bold_style)
    ws.write(0, 4, "Cohort", bold_style)
    ws.write(0, 5, "Attempt", bold_style)
    ws.write(0, 6, "Started", bold_style)
    ws.write(0, 7, "Ended", bold_style)
    ws.write(0, 8, "Time in Test (hrs)", bold_style)
    ws.write(0, 9, "Approval", bold_style)

    for i, student in enumerate(data):
        start_time = cmpt.format_time(student.get('time_started'))
        end_time = cmpt.format_time(student.get('time_ended'))
        if access_type == 'admin':
            identifier=student.get('mthscID')
        else:
            identifier=cmpt.get_name_from_xid(student.get('xid'))

        ws.write(i + 1, 0, student.get('xid'))
        ws.write(i + 1, 1, student.get('username'))
        ws.write(i + 1, 2, identifier)
        ws.write(i + 1, 3, student.get('score'))
        ws.write(i + 1, 4, student.get('ALEKS_class_code'))
        ws.write(i + 1, 5, student.get('test_number'))
        ws.write(i + 1, 6, f"""{student.get('date_started').strftime("%m-%d-%Y")} {start_time}""")
        ws.write(i + 1, 7, f"""{student.get('date_ended').strftime("%m-%d-%Y")} {end_time}""")
        ws.write(i + 1, 8, student.get('time_in_test'))
        if access_type == 'admin':
            ws.write(i + 1, 9, student.get('approval'))

    # we should size the columns appropriately here
    wb.save(xl_buffer)

    # print the buffer to the response header
    # print(xl_buffer.getvalue())

    writer.close()
    xl_buffer.seek(0)

    return send_file(xl_buffer, attachment_filename=f"""{cohort_selection}-{score_type}.xlsx""", as_attachment=True)


