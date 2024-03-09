#!/var/www/mthsc/common/venv/bin/python3

from cmpt_lib import cmpt_lib
# import ftplib as ftp
from io import StringIO
import os
from pathlib import Path

syspath=os.path.dirname(__file__)

cmpt=cmpt_lib()

cur_semester = cmpt.get_current_semester()
cur_year = cmpt.get_current_year()

class_codes = cmpt.get_recent_ALEKS_class_codes(cur_semester, cur_year)

for class_code in class_codes:
    cmpt.download_all_ALEKS_scores(class_code)

# we should be using a get all scores method (? not sure about this)
scores = cmpt.get_orientation_scores()

score_output = ""

for score in scores:
    # we need to actually figure out the expiration date better
    #score_output += "%s\t%s\t%s\n" % (score["xid"], str(score["score"]).zfill(3), cmpt.get_cmpt_expiration_term(score["date_ended"]))


    # we send the scores as 3 digits padded with leading zeros since the "sophisticated" Banner system doesn't handle normal integers apparently
    # NB: (6-21-2014) we no longer send an expiration date
    score_output += f"""{score.get('xid')}\t{str(score.get('score')).zfill(3)}\n"""

#KH 6-9-16 Added substitute scores to file
sub_score_list = cmpt.get_substitute_scores()

for sub_score in sub_score_list:
	score_output += f"""{sub_score.get('xid')}\t{str(sub_score.get('score')).zfill(3)}\n"""

# for testing
#output = open("scores.txt", "w")
#output.write(score_output)
#output.close()

buffer = StringIO(score_output)

# store the file 
# file = open("/home/dapxfer/files/cmpt_scores.txt", "w")
filename=os.path.join(syspath,"app/static/reports/cmpt_scores.txt")
file = open(filename,"w")
file.write(buffer.getvalue())
file.close()
buffer.close()

# old connection
#con = ftp.FTP("ftp.netware.clemson.edu")
#con.login(".dapftp.d.misc.clemsonu", "ftpid")
#con.storlines("STOR cmpt_scores.txt", buffer)
#con.close()

