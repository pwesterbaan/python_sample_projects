#!/var/www/mthsc/common/venv/bin/python3

import os

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cgsg_senators_bp
from .vote_lib import vote_lib
from extensions import htpasswd, sess

vl=vote_lib()

@cgsg_senators_bp.route('/stats',methods=['GET','POST'])
@htpasswd.required
def stats(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']='CGSG Senator Vote'

    stats = vl.get_stats()

    total_votes = stats.get('total_votes')

    crunkleton = stats.get('crunkleton')
    crunkleton_percent = float(crunkleton)/total_votes
    finney = stats.get('finney')
    finney_percent = float(finney)/total_votes
    sotherden = stats.get('sotherden')
    sotherden_percent = float(sotherden)/total_votes

    # generate the stats table
    content = f"""
<div>Total votes: {total_votes:1.0f}</div>
<table class="voting">
    <tr>
        <td>Candidate</td>
        <td>Votes (%%)</td>
    </tr>
    <tr>
        <td>Joshua Crunkleton</td>
        <td>{crunkleton:1.0f} / {total_votes:1.0f} ({crunkleton_percent:3.2%})</td>
    </tr>
    <tr>
        <td>Michael Finney</td>
        <td>{finney:1.0f} / {total_votes:1.0f} ({finney_percent:3.2%})</td>
    </tr>
    <tr>
        <td>Elaine Sotherden</td>
        <td>{sotherden:1.0f} / {total_votes:1.0f} ({sotherden_percent:3.2%})</td>
    </tr>
</table>
"""

    kwargs['content']=Markup(content)
    return render_template("cgsg_senators/stats.html",**kwargs)
