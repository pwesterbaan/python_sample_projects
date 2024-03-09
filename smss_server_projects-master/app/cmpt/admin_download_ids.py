#!/var/www/mthsc/common/venv/bin/python3

import csv
import datetime
import io
import pandas as pd
import time
import xlwt

from flask import Flask, Blueprint, send_file

from . import cmpt_bp
from .cmpt_lib import cmpt_lib
from extensions import htpasswd, sess

cmpt=cmpt_lib()

@cmpt_bp.route('/admin_download_ids',methods=['POST'])
@htpasswd.required
def admin_download_ids(user):
    xl_buffer = io.BytesIO()
    writer = pd.ExcelWriter(xl_buffer,engine='xlsxwriter')

    wb = xlwt.Workbook()
    ws = wb.add_sheet("scores")

    if score_type == "best":
        data = cmpt.get_best_scores(cohort_selection)
    else:
        data = cmpt.get_all_scores(cohort_selection)

    bold_style = xlwt.easyxf("font: bold on")
    ws.write(0, 0, "XID", bold_style)
    ws.write(0, 1, "MthSC ID", bold_style)
    ws.write(0, 2, "Username", bold_style)

    for i, student in enumerate(data):
        ws.write(i + 1, 0, student.get('xid'))
        ws.write(i + 1, 1, student.get('mthscID'))
        ws.write(i + 1, 2, student.get('username'))

    # we should size the columns appropriately here
    wb.save(xl_buffer)

    writer.close()
    xl_buffer.seek(0)

    return send_file(xl_buffer, attachment_filename=f"""CMPT_IDs.xlsx""", as_attachment=True)
