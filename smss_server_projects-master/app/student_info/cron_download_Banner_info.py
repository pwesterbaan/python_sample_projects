#!/var/www/mthsc/common/venv/bin/python3

import student_info_lib as sil

sil.download_student_info(sil.get_current_semester(), sil.get_current_year())
sil.download_accepted_student_info(sil.get_current_term())
sil.download_accepted_student_info(sil.get_next_term())

