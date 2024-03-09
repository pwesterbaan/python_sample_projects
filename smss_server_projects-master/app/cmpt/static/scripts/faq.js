$(window).load(function () {
    setup_faq();
});

function setup_faq()
{
    $("dd").hide();
    $("dt").bind("click", function(){
        $(this).toggleClass("open").next().slideToggle(250); 
    });
}
