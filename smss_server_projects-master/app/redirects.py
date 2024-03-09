from flask import Flask, Blueprint, redirect, url_for, flash, abort, session, request, json
import os

redirects_bp=Blueprint('redirects',__name__)

#strip extensions from old page names and redirect
@redirects_bp.route('/<path:pagename>.py',methods=['GET','POST'])
def strip_py_and_redirect(pagename):
    try:
        # print('strip_py_and_redirect')
        # print(f'request: {request}')
        flash("""URL's ending in '.py' are now being redirected...""")
        return redirect(url_for(pagename.replace('/','.')), code=307)
    except:
        session['_flashes'].clear()
        flash(f"Requested page ({pagename}) does not exist")
        return abort(404, f"The requested URL ({pagename}) was not found on the server. If you entered the URL manually please check your spelling and try again.")

@redirects_bp.app_errorhandler(404)
def page_not_found(e):
    # print(f"""main errorhandler: {request.url}""")
    # redirect_target='/'.join(request.url.split('/')[1:-1])
    # print(f"""Target: {redirect_target}""")
    # flash(f"""{e}\nYou have been redirected to the landing page...""")
    # return redirect(redirect_target)
    return redirect('/index.html')
