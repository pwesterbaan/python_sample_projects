#!/var/www/mthsc/common/venv/bin/python3

from cmpt_lib import cmpt_lib
cmpt=cmpt_lib()

cur_semester = cmpt.get_current_semester()
cur_year = cmpt.get_current_year()

class_codes = cmpt.get_recent_ALEKS_class_codes(cur_semester, cur_year)

for class_code in class_codes:
    cmpt.download_all_ALEKS_scores(class_code)

