import os

from app import app

import socket

HOST = '127.0.0.1'  # Standard loopback interface address (localhost)
PORT = 65432        # Port to listen on (non-privileged ports are > 1023)

#----------------------------------------
# launch
#----------------------------------------



if __name__ == "__main__":
    # port = int(os.environ.get("PORT", 5000))
    # app.run(host='0.0.0.0', port=port, debug=False)
    app.run()