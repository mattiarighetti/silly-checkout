let btns = document.querySelectorAll(".sidebar-link");

for (const element of btns) {
    element.addEventListener("click", function () {
        document.querySelectorAll(".sidebar-link.active").forEach((btn) => {
            btn.classList.remove("active");
        });

        this.classList.add("active");

        document.querySelectorAll(".page.active").forEach((page) => {
            page.classList.remove("active");
        });

        document.getElementById(`${this.dataset.target}`).classList.add("active");
    });
}
