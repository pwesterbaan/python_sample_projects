from flask import Flask, Blueprint, redirect, url_for, flash, abort, session

from app.candidate_pages import dept_forms_bp

#default to 
@dept_forms_bp.route('/')
def home():
    return redirect('candidate_list')

#strip extensions from old page names and redirect
@dept_forms_bp.route('/<pagename>.py')
@dept_forms_bp.route('/<pagename>.html')
def strip_py_and_redirect(pagename):
    try:
        flash("""URL's ending in '.py' are now being redirected...""")
        # return redirect(url_for(f'{pagename}.{pagename}'))
        return redirect(pagename)
    except:
        session['_flashes'].clear()
        flash(f"Requested page ({pagename}) does not exist")
        return abort(404, f"The requested URL ({pagename}) was not found on the server. If you entered the URL manually please check your spelling and try again.")

@dept_forms_bp.app_errorhandler(404)
def page_not_found(e):
    flash(f"""{e}\nYou have been redirected to the landing page...""")
    return redirect("/dept_forms/") #TODO -- use when 'flask run' handles pages
    # return redirect(url_for('information.information')) #TODO -- when apache handles page
