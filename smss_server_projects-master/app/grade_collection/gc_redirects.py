from flask import Flask, Blueprint, redirect, url_for, flash, abort
gc_redirects_bp=Blueprint('gc_redirects',__name__)

#default to 
@gc_redirects_bp.route('/')
def home():
    return redirect(url_for('main.main'))

#strip extensions from old page names and redirect
@gc_redirects_bp.route('/<pagename>.py')
@gc_redirects_bp.route('/<pagename>.html')
def strip_py_and_redirect(pagename):
    try:
        flash("""URL's ending in '.py' are now being redirected...""")
        if pagename not in ['main']:
            return redirect(url_for('{pagename}.{pagename}'))
        else:
            return redirect(url_for('main.main'))
    except:
        flash(f"Requested page ({pagename}) does not exist")
        return abort(404, f"The requested URL ({pagename}) was not found on the server. If you entered the URL manually please check your spelling and try again.")

#redirect failed urls to gc main
@gc_redirects_bp.app_errorhandler(404)
def page_not_found(e):
    flash(f"""{e} Redirected to landing page...""")
    # return redirect("/grade_collection/main")
    return redirect(url_for('main.main'))
