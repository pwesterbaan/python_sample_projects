#!/var/www/mthsc/common/venv/bin/python3

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import dept_forms_bp
from .candidate_lib import candidate_lib
from extensions import htpasswd, sess

cand_lib=candidate_lib()

@dept_forms_bp.route('/manage_pool',methods=['GET','POST'])
@htpasswd.required
def manage_pool(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"

    form             = request.form
    action           = form.get('action')
    form_name        = form.get('form_name')
    pool_id          = form.get('pool_id', cand_lib.get_current_pool_id())
    pool_description = form.get('description')

    candidate_id = form.get('candidate_id')
    close_date   = form.get('close_date')
    first_name   = form.get('first_name')
    last_name    = form.get('last_name')
    open_date    = form.get('open_date')
    school       = form.get('school')
    visit_dates  = form.get('visit_dates')

    #Default to redirect back to pool_list after edit/deletion/etc. completed
    content = """<html>
<head>
    <meta HTTP-EQUIV="REFRESH" content="0; url=pool_list">
</head>
<body>
<p style="text-align: center;">Please wait while you are redirected back to the pool list page, or click <a href="pool_list">here</a> to go there now.</p>
</body>
</html>"""

    if request.method == 'GET':
        #Setting default behaivor
        action='add'

    return_content=False

    if form_name == "pool_add":
        return_content=True
        cand_lib.add_pool(pool_description)

    elif form_name == "pool_edit":
        return_content=True
        cand_lib.update_pool(pool_description)

    if action == "delete":
        return_content=True
        result = cand_lib.delete_pool(pool_id)
        if result == False:
            content = """<html>
<head>
    <title>Error</title>
</head>
<body>
<p style="text-align: center; width: 600px; margin: 50px auto; border: solid 2px #000000; background: #FF9966; padding: 20px;">The candidate pool could not be deleted since there are some candidates in it. You must remove all candidates from a pool before deleting it. Click <a href="pool_list">here</a> to return to the candidate pool list page.</p>
</body>
</html>"""

    elif action == "add":
        title = "Add\u00A0Candidate\u00A0Pool"
        form_action = "pool_add"
        btn_name = "Add"
        pool = { "description": "" }

    elif action == "edit":
        title = "Edit\u00A0Candidate\u00A0Pool"
        form_action = "pool_edit"
        btn_name = "Update"
        pool = cand_lib.get_pool_info(pool_id)
        

    if return_content:
        #display error message or redirect to candidate_list
        return content
    
    # render empty template

    kwargs['btn_name']     = Markup(btn_name)
    kwargs['description']  = Markup(pool.get('description'))
    kwargs['form_action']  = Markup(form_action)
    kwargs['pool_id']      = Markup(str(pool_id))
    kwargs['title']        = Markup(title)
    
    return render_template("manage_pool.html", **kwargs)
