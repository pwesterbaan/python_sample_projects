#!/var/www/mthsc/common/venv/bin/python3
import sys, os, site
from pathlib import Path

#site.addsitedir('/var/www/mthsc/common/venv/lib/python3.6/site-packages'))

#grab directory this file lives in
syspath=os.path.dirname(os.path.abspath(__file__))
site.addsitedir(os.path.join(syspath,'venv/lib/python3.6/site-packages'))
sys.path.insert(0, syspath)
home=syspath

from app import app as application
