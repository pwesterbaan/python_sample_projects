function warning(id, student_type)
/* function warning(id) */
{
    response = confirm("Are you sure you want to remove this person from the eligibility list?");

    if(response)
    {
        /*window.location.href = "admin_view_students.py?delete=" + id + "&student_type=" + student_type;*/
        // window.location.href = "admin_view_students?mthscID=" + id;

	// post() is defined in cmpt_functions.js
	post('/admin_view_students', {mthscID: id, student_type: student_type});
        return true;
    }
    else
    {
        return false;
    }
}

/*function warning_all(student_type)
  {
  response = confirm("Are you sure you want to remove ALL the people from the eligibility list of type: " + student_type + "?");

  if(response)
  {
  window.location.href = "admin_view_students.py?delete=*&student_type=" + student_type;
  return true;
  }
  else
  {
  return false;
  }
  }*/
