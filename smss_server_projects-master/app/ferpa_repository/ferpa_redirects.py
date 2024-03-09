from flask import Flask, Blueprint, redirect, url_for, flash, abort
ferpa_redirects_bp=Blueprint('ferpa_redirects',__name__)

#default to ferpa_view
@ferpa_redirects_bp.route('/')
def home():
    return redirect(url_for('ferpa_view.ferpa_view'))

#strip extensions from old page names and redirect
@ferpa_redirects_bp.route('/<pagename>.py')
@ferpa_redirects_bp.route('/<pagename>.html')
def strip_py_and_redirect(pagename):
    try:
        flash("""URL's ending in '.py' are now being redirected...""")
        if pagename not in ['ferpa_view']:
            return redirect(url_for('{pagename}.{pagename}'))
        else:
            return redirect(url_for('ferpa_view.ferpa_view'))
    except:
        flash(f"Requested page ({pagename}) does not exist")
        return abort(404, f"The requested URL ({pagename}) was not found on the server. If you entered the URL manually please check your spelling and try again.")

# redirect failed urls to ferpa_view
@ferpa_redirects_bp.app_errorhandler(404)
def page_not_found(e):
    # flash(str(e)) # <-- Produces very long and ugly message
    # return redirect(url_for('ferpa_view.ferpa_view'))
    return redirect('/ferpa_repository/')
