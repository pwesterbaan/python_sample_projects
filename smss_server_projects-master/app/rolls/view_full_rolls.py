#!/var/www/mthsc/common/venv/bin/python3

from . import rolls_bp
import rolls_lib as rl
import datetime

today = datetime.datetime.now()

print("Mime-Version: 1.0\r")
print("Content-Disposition: attachment; filename=rolls_%s.txt;\r" % today.strftime("%y%m%d_%H%M"))
print("Content-Type: text/plain\r\n\r")

rolls = rl.get_full_rolls()

for record in rolls:
    print("%s\t%s\t%s\t%s\t%s\t%s\t%s" % (record[0], record[1], record[2], record[3], record[4], record[6], record[5]))
