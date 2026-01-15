document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelector(".tabs[role='tablist']");
    const tabsList = tabs.querySelectorAll(".tab");
    tabsList.forEach((tab) => {
        tab.addEventListener("click", (e) => {
            e.preventDefault();

            const tabIndex = tab.dataset.index;

            if (tabIndex == undefined) {
                document
                    .querySelectorAll("article.product")
                    .forEach((product) => {
                        product.classList.remove("d-none");
                    });
                document
                    .querySelectorAll(".tabs[role='tablist'] .tab")
                    .forEach((tab, index) => {
                        if (index === 0) {
                            tab.classList.add("is-active");
                        } else {
                            tab.classList.remove("is-active");
                        }
                    });
            } else {
                tab.classList.add("is-active");
                document
                    .querySelectorAll(".tabs[role='tablist'] .tab")
                    .forEach((otherTab) => {
                        if (otherTab !== tab) {
                            otherTab.classList.remove("is-active");
                        }
                    });
                document
                    .querySelectorAll("article.product")
                    .forEach((product) => {
                        if (product.getAttribute("tabindex") === tabIndex) {
                            product.classList.remove("d-none");
                        } else {
                            product.classList.add("d-none");
                        }
                    });
            }
        });
    });
});
