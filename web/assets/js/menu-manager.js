function toggleMenuBox() {
    var menuBox = document.getElementById("menu-box");
    var menu = document.getElementById("menu");
    if(menuBox.innerHTML === "Deactivated") {
        menuBox.innerHTML = "Activated";
        menuBox.setAttribute("class", "box box-deactivated cursor-hand");
        menu.setAttribute("class", "menu menu-activated");
    } else {
        menuBox.innerHTML = "Deactivated";
        menuBox.setAttribute("class", "box box-activated cursor-hand");
        menu.setAttribute("class", "menu menu-deactivated");
    }
}