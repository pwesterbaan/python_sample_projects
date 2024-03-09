#!/var/www/mthsc/common/venv/bin/python3

from cmpt_lib import cmpt_lib
cmpt=cmpt_lib()

term = cmpt.get_current_term()
cmpt.download_admissions_list_from_daps(term)
