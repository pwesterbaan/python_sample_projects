#!/var/www/mthsc/common/venv/bin/python3

import os

from flask import Flask, render_template, request, Markup, Blueprint, make_response

from . import cgsg_senators_bp
from .vote_lib import vote_lib
from extensions import htpasswd, sess

vl=vote_lib()

@cgsg_senators_bp.route('/ballot',methods=['GET','POST'])
@htpasswd.required
def ballot(user):
    kwargs={}
    kwargs['debug']="use this to print vars when debugging"
    kwargs['title']="CGSG Senator Election"

    form       = request.form
    form_name  = form.get('form_name')

    display_names = ["Joshua Crunkleton", "Michael Finney", "Elaine Sotherden"]
    names = ["crunkleton", "finney", "sotherden"]

    if vl.is_eligible(user):
        # check if the form is being submitted
        if form_name == "cgsg_senators":
            # save their responses
            votes = []
            vote_total = 0

            for name in names:
                cur_vote = form.get(name,0)
                if cur_vote != 0:
                    cur_vote = 1
                vote_total += cur_vote
                votes.append(cur_vote)

            if vote_total > 2:
                content="You can vote for at most 2 candidates. Please return to the voting page and resubmit your vote."
            else:
                vl.save_response(user, votes[0], votes[1], votes[2])
                content = "Your vote has been saved. You can close this page."

            kwargs['content'] = Markup(content)
            return render_template("message.html",**kwargs)
        else:
            # show the voting form
            content = """
    <table class="names_table">
        <tr>
            <td>Vote</td>
            <td>Candidate</td>
        </tr>
"""

            for i in range(0, len(display_names)):
                content += f"""
        <tr>
            <td>
                <input type="checkbox" name="{names[i]}" value="1">
            </td>
            <td>{display_names[i]}</td>
        </tr>
"""

            content += """
    </table>
"""

    else:
        content = "You are not allowed to vote. Either you are not a current graduate student or you have already voted. If you feel that you should be able to vote, please contact Chris Cox (clcox@clemson.edu)."

    kwargs['content'] = Markup(content)
    return render_template("cgsg_senators/ballot.html",**kwargs)
