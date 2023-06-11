/** @format */

new Sortable(document.querySelector("#origin"), {
    group: "shared",
    animation: 150,
});

function updateList() {
    let cibles = document.querySelectorAll("#cible .list-group-item");
    let list = document.querySelector("form .list input");
    let newList = [];
    cibles.forEach((cible) => {
        let current = cible.querySelector("input");
        newList.push({ index: current.name, value: current.value });
    });
    list.value = JSON.stringify(newList);
    console.log(list, newList);
}

new Sortable(document.querySelector("#cible"), {
    group: "shared",
    animation: 150,
    onEnd: function (evt) {
        updateList();
    },
    onSort: function (evt) {
        updateList();
    },
});

let renames = document.querySelectorAll(".list-group-item .rename");
renames.forEach(
    (rename) =>
        function (evt) {
            rename.addEventListener("change", function (evt) {
                updateList();
            });
        }
);

// gestion des évènement sur le drag and dropn choix chazmp + réorg + rename
let cible = document.querySelector("#cible");
cible.addEventListener("drop", (evt) => {
    console.log(evt);
});
