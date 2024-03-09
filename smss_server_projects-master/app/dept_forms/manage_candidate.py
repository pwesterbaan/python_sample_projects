#!/var/www/mthsc/common/venv/bin/python3

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import dept_forms_bp
from .candidate_lib import candidate_lib
from extensions import htpasswd, sess

cand_lib=candidate_lib()

@dept_forms_bp.route('/manage_candidate',methods=['GET','POST'])
@htpasswd.required
def manage_candidate(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Manage\00A0Candidate"

    form         = request.form
    action       = form.get('action','add')
    candidate_id = form.get('candidate_id')
    close_date   = form.get('close_date')
    first_name   = form.get('first_name')
    form_name    = form.get('form_name')
    last_name    = form.get('last_name')
    open_date    = form.get('open_date')
    pool_id      = form.get('pool_id', cand_lib.get_current_pool_id())
    school       = form.get('school')
    visit_dates  = form.get('visit_dates')

    #Default to redirect back to candidate_list after edit/deletion/etc. completed
    content = """<html>
<head>
    <meta HTTP-EQUIV="REFRESH" content="0; url=candidate_list">
</head>
<body>
<p style="text-align: center;">Please wait while you are redirected back to the candidate list page, or click <a href="candidate_list">here</a> to go there now.</p>
</body>
</html>"""

    return_content=False

    if form_name == "candidate_add":
        return_content=True
        cand_lib.add_candidate(pool_id, first_name, last_name, school, visit_dates, open_date, close_date)

    elif form_name == "candidate_edit":
        return_content=True
        cand_lib.update_candidate(candidate_id, pool_id, first_name, last_name, school, visit_dates, open_date, close_date)

    if action == "delete":
        return_content=True
        result = cand_lib.delete_candidate(candidate_id)
        if result == False:
            content = """<html>
<head>
    <title>Error</title>
</head>
<body>
<p style="text-align: center; width: 600px; margin: 50px auto; border: solid 2px #000000; background: #FF9966; padding: 20px;">The candidate could not be deleted since some people have already filled out an evaluation for him. Click <a href="candidate_list">here</a> to return to the candidate list page. If this candidate really does need to be deleted, contact Kevin Hedetniemi (hedetni@clemson.edu).</p>
</body>
</html>"""
    elif action == "add":
        title = "Add Candidate"
        form_action = "candidate_add"
        btn_name = "Add"
        candidate = {
            "pool_id": cand_lib.get_current_pool_id(),
            "first_name": "",
            "last_name": "",
            "school": "",
            "visit_dates": "",
            "open_date": "",
            "close_date": ""
        }

    elif action == "edit":
        title = "Edit Candidate"
        form_action = "candidate_edit"
        btn_name = "Update"
        candidate = cand_lib.get_candidate_info(candidate_id)
        candidate["open_date"] = candidate.get('open_date').strftime("%m/%d/%y").lstrip("0")
        candidate["close_date"] = candidate.get('close_date').strftime("%m/%d/%y").lstrip("0")

    if return_content:
        #display error message or redirect to candidate_list
        return content
    
    # render empty template
    pool_ids = []
    pool_descriptions = []

    for pool in cand_lib.get_pool_list():
        pool_ids.append(pool["pool_id"])
        pool_descriptions.append(pool["description"])

    pool_dropdown_html = cand_lib.get_dropdown_html(pool_ids, pool_descriptions, candidate.get('pool_id'), "pool_id")

    kwargs['btn_name']      = Markup(btn_name)
    kwargs['candidate']     = candidate
    kwargs['candidate_id']  = Markup(str(candidate_id))
    kwargs['form_action']   = Markup(form_action)
    kwargs['pool_dropdown'] = Markup(pool_dropdown_html)
    kwargs['title']         = Markup(title)
    
    return render_template("manage_candidate.html", **kwargs)
