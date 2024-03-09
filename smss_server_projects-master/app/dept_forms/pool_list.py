#!/var/www/mthsc/common/venv/bin/python3

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import dept_forms_bp
from .candidate_lib import candidate_lib
from extensions import htpasswd, sess

cand_lib=candidate_lib()

@dept_forms_bp.route('/pool_list',methods=['GET','POST'])
@htpasswd.required
def pool_list(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="Candidate\u00A0Pool\u00A0List"

    form       = request.form
    pool_id    = form.get('pool_id', cand_lib.get_current_pool_id())

    pool_list = cand_lib.get_pool_list()

    if len(pool_list) > 0:
        pool_list_html = "\n\t".join([f"""
<div class="candidate">
    <div style="float: right;">
      <ul style="list-style: none; margin-top: 0px; padding-top: 0px;">
        <li>
          <form action="manage_pool" method="POST">
            <input type="hidden" name="pool_id" value="{item.get('pool_id')}">
            <input type="hidden" name="action" value="edit">
            <input type="submit" value="Edit Pool">
          </form>
        </li>
        <li>
          <form action="manage_pool" method="POST">
            <input type="hidden" name="pool_id" value="{item.get('pool_id')}">
            <input type="hidden" name="action" value="delete">
            <input type="submit" value="Delete Pool"></li>
          </form>
        </li>
      </ul>
    </div>
    <div class="name">{item.get('description')}</div>
</div>""" for item in pool_list])
    else:
        pool_list_html = "There are no candidate pools setup. To setup a pool click the Add New Pool link above."

    kwargs['pool_list']   = Markup(pool_list_html)
    
    return render_template("pool_list.html", **kwargs)
