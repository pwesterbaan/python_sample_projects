from flask import Flask
from flask.views import View

app = Flask(__name__)


class HelloWorld(View):

    def dispatch_request(self):
        return 'Hello World!'

class HelloUser(View):

    def dispatch_request(self, name):
        return f'Hello {name}'

app.add_url_rule('/hello', view_func=HelloWorld.as_view('hello_world'))
app.add_url_rule('/hello/<string:name>', view_func=HelloUser.as_view('hello_user'))

if __name__ == "__main__":
    app.run(host='0.0.0.0', debug=True)
