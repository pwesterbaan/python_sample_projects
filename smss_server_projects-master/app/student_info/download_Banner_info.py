#!/var/www/mthsc/common/venv/bin/python3
import daemon
import sys
import time

sys.path.append("/var/www/mthsc/html/app/student_info")
import student_info_lib as sil
sil=sil.student_info_lib()

with daemon.DaemonContext():
    sil.download_student_info(sil.get_current_semester(), sil.get_current_year())


######### OLD VERSION ######### 2022-12-11 PW
# from daemon import Daemon

# # class to hold the long running process
# class Banner_process(Daemon):
#     def run(self):
#         #file = open("/var/www/mthsc/html/prereq/test.txt", "w")

#         #for i in range(0,10):
#         #    file.write("%i...\n" % i)
#         #    time.sleep(5)

#         #file.close()
#         sil.download_student_info(sil.get_current_semester(), sil.get_current_year())


# daemon = Banner_process("/tmp/python_Banner_download.pid")
# daemon.start()



# nothing runs after here since the process is terminated during daemonization
