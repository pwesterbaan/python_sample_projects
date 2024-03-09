var cur_id = 0;
var deleted_items = new Array();
var updated_items = new Array();
var added_items = new Array();
const svg_data = '<svg width="13" height="13" viewBox="0 0 11.5 11.5" id="red_minus" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><circle style="fill:#ff0000;" cx="5.2916665" cy="5.2916665" r="5.2916665"/><rect style="fill:#ffffff;" width="5.2916665" height="1.5875" x="2.6458333" y="4.4979167"/></svg>';

tinymce.init({
    selector: '.course_page_editor',
    promotion: false,
    menubar: false,
    plugins: [
	'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview','anchor', 'pagebreak', 'searchreplace', 'wordcount', 'visualblocks','visualchars', 'code', 'fullscreen', 'insertdatetime', 'table', 'emoticons','template', 'help'
    ],
    toolbar: 'bold italic underline styleselect | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify | link | forecolor backcolor emoticons | undo redo removeformat | help preview | deleteItem',
    link_list: window.location.href+"/course_page_ajax",
    width: "100%",
    setup: (editor) => {
	editor.on('change', ()=> update_content(editor)),
	editor.ui.registry.addIcon('red_minus_icon', svg_data),
	editor.ui.registry.addButton('deleteItem', {
	    text: 'Delete Item', 
	    tooltip: 'Delete Item',
	    icon: 'red_minus_icon',
	    onAction: () => del_content(editor)
	},);
    },
});

function add_content(cat_id)
{
    //increment cur_id
    cur_id++;
    
    var new_content_name = "new_" + cat_id + "_" + cur_id;

    var container = document.getElementById(cat_id);

    var new_content = document.createElement('div');
    new_content.style.margin = "20px";

    var new_textarea = document.createElement('textarea');
    new_textarea.name = new_content_name;
    new_textarea.id = new_content_name;
    new_textarea.className = "course_page_editor";

    new_content.appendChild(new_textarea);

    container.appendChild(new_content);

    tinymce.init({
	selector: '.course_page_editor',
	promotion: false,
	menubar: false,
	plugins: [
	    'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview','anchor', 'pagebreak', 'searchreplace', 'wordcount', 'visualblocks','visualchars', 'code', 'fullscreen', 'insertdatetime', 'table', 'emoticons','template', 'help'
	],
	toolbar: 'bold italic underline styleselect | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify | link | forecolor backcolor emoticons | undo redo removeformat | help preview | deleteItem',
	link_list: window.location.href+"/course_page_ajax",
	width: "100%",
	setup: (editor) => {
	    editor.on('change', ()=> update_content(editor)),
	    editor.ui.registry.addIcon('red_minus_icon', svg_data),
	    editor.ui.registry.addButton('deleteItem', {
		text: 'Delete Item', 
		tooltip: 'Delete Item',
		icon: 'red_minus_icon',
		onAction: () => del_content(editor)
	    },);
	},
    });

    added_items.push(new_content_name);
}

function del_content(editor)
{
    var id=editor.targetElm.id;
    var cur_content = document.getElementById(id).parentNode;
    cur_content.remove();

    if(id.indexOf("new") == -1) // this is an old item
    {
	// record item as deleted
	deleted_items.push(id);
    }
    else // this is a new item
    {
	// remove the item from the added list
	for(var i = 0; i < added_items.length; ++i)
	{
	    if(added_items[i] == id)
	    {
		added_items.splice(i,1);
		break;
	    }
	}
    }
}

function generate_change_list()
{
    document.getElementById("update_list").value = updated_items.join(";");
    document.getElementById("delete_list").value = deleted_items.join(";");
    document.getElementById("add_list").value = added_items.join(";");

    return true;
}

function update_content(editor)
{
    id=editor.targetElm.id;
    if (!updated_items.includes(id)){
	updated_items.push(id);
    }
}
