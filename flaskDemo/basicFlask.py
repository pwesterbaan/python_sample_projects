from flask import Flask, render_template
app = Flask(__name__)

@app.route("/")
def index():
    return "Index!"

@app.route("/hello")
def helloWorld():
    return "Hello World!"

@app.route("/members")
def members():
    return "Members"

@app.route("/members/<string:name>/")
def getMember(name):
    return name

@app.route("/hello/<string:name>/")
def helloName(name):
    return render_template('test.html', name=name)
    # return render_template('test.html', **locals())

if __name__ == "__main__":
    app.run()
