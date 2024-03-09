from flask import Flask, Blueprint, redirect, url_for, flash, abort, session

from . import cmpt_bp
# cmpt_redirects_bp=Blueprint('cmpt_redirects',__name__)

#default to
@cmpt_bp.route('/')
def home():
    return redirect(url_for('cmpt.information'))

#redirect copied from old system
@cmpt_bp.route('/score_login.py')
@cmpt_bp.route('/score_login')
def redirect_score_login():
    return redirect(url_for('cmpt.view_cmpt_score'))

# redirect failed urls to cmpt info page
# @cmpt_bp.app_errorhandler(404)
# def page_not_found(e):
#     print('cmpt errorhandler')
#     flash(f"""{e}\nYou have been redirected to the landing page...""")
#     # return redirect(url_for('cmpt.information'))
#     return redirect('/')
