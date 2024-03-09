#!/usr/bin/python3
#__init__.py
import datetime
from pathlib import Path
import os

from flask import Flask, render_template, Markup, flash, Blueprint, current_app, session
from flask_cors import CORS
from flask_sso import SSO
import flask_excel as excel
from flask_wtf import FlaskForm
from flask_session import Session

from extensions import htpasswd, sess#, sso_ext

from .cmpt import cmpt_bp
from .dept_forms import dept_forms_bp
from .ferpa_repository import ferpa_repository_bp
from .grade_collection import grade_collection_bp
from .redirects import redirects_bp
from .rolls import rolls_bp
# from .shib import shib_bp
from .student_info import student_info_bp
from .ug_course_pages import ug_course_pages_bp

def create_app():
    app = Flask(__name__, template_folder="templates")

    #import subdirectories
    blueprints=[
        cmpt_bp,
        dept_forms_bp,
        ferpa_repository_bp,
        grade_collection_bp,
        redirects_bp,
        rolls_bp,
        student_info_bp,
        ug_course_pages_bp,
    ]
    for bp in blueprints: app.register_blueprint(bp)

    #current working directory is up one level from app folder
    cwd=Path(os.path.dirname(__file__))
    # options are:
    # ProductionConfig, DevelopmentConfig, TestingConfig
    app.config.from_object("flaskConfig.DevelopmentConfig") 
    app.config['COURSE_PAGE_FILE_PATH'] = os.path.join(cwd,'ug_course_pages/static/uploaded_items')
    app.config['FERPA_UPLOAD_PATH'] = os.path.join(cwd,'ferpa_repository/ferpa_forms')
    app.config['GC_REPORTS_FOLDER']= os.path.join(cwd,'grade_collection/static/reports')
    app.config['SESSION_FILE_DIR'] = os.path.join(cwd,'flask_session/')
    app.config.from_object(__name__)
    htpasswd.init_app(app) #Basic authentication (not SSO)
    excel.init_excel(app)  #Used for generating csv file
    sess.init_app(app)     #Session management
    
    return app
app = create_app()
