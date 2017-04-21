$("#main-menu .dropdown").mouseover(function (event) {
    $(this).find(".dropdown-menu").show();
}).mouseleave(function () {
    $(this).find(".dropdown-menu").hide();
});

