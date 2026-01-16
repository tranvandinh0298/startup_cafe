// tab sản phẩm
document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelector(".tabs[role='tablist']");
    if (tabs) {
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
    }
});

//
document.addEventListener("DOMContentLoaded", () => {
    const cart = new Map();
    /*
cart = Map<
  productId,
  {
    productId: number,
    name: string,
    price: number,
    qty: number
  }
>
*/

    document.querySelector(".grid").addEventListener("click", (e) => {
        const productEl = e.target.closest(".product");
        if (!productEl) return;

        const productId = productEl.dataset.index;
        const name = productEl.querySelector(".product__name").innerText.trim();
        const priceText = productEl.querySelector(".price").innerText.trim();
        const price = parsePrice(priceText);

        if (cart.has(productId)) {
            cart.get(productId).qty += 1;
        } else {
            cart.set(productId, { productId, name, price, qty: 1 });
        }

        renderCart();
    });

    const renderCart = () => {
        const cartBody = document.querySelector(".cart__body");

        if (cartBody) {
            cartBody.innerHTML = "";

            if (cart.size === 0) {
                cartBody.innerHTML = `
            <div class="empty">
                <b>No items yet</b>
                <span>Click a product to add into the cart.</span>
            </div>
        `;
                updateSummary();
                return;
            }

            cart.forEach((item) => {
                const itemTotal = item.price * item.qty;

                const div = document.createElement("div");
                div.className = "item";
                div.dataset.name = item.name;
                div.dataset.productId = item.productId;

                div.innerHTML = `
            <div class="item__main">
                <div class="item__name">${item.name}</div>
                <div class="item__sub">
                    <span>${formatPrice(item.price)} × ${item.qty}</span>
                    <span><b>${formatPrice(itemTotal)}</b></span>
                </div>
            </div>

            <div class="qty">
                <button class="qty__btn" data-action="dec">−</button>
                <div class="qty__num">${item.qty}</div>
                <button class="qty__btn" data-action="inc">+</button>
                <button class="qty__remove" data-action="remove">×</button>
            </div>
        `;

                cartBody.appendChild(div);
            });

            updateSummary();
        }
    };

    document.querySelector(".cart__body").addEventListener("click", (e) => {
        const actionBtn = e.target.closest("[data-action]");
        if (!actionBtn) return;

        const itemEl = e.target.closest(".item");
        if (!itemEl) return;

        const productId = itemEl.dataset.productId;
        const item = cart.get(productId);

        switch (actionBtn.dataset.action) {
            case "inc":
                item.qty += 1;
                break;

            case "dec":
                item.qty -= 1;
                if (item.qty <= 0) cart.delete(productId);
                break;

            case "remove":
                cart.delete(productId);
                break;
        }

        renderCart();
    });

    const updateSummary = () => {
        let subtotal = 0;

        cart.forEach((item) => {
            subtotal += item.price * item.qty;
        });

        const summary = document.querySelector(".cart__footer .sum");
        const rows = summary.querySelectorAll(".row");

        // Subtotal
        rows[0].querySelector("b").innerText = formatPrice(subtotal);

        // Discount (hardcoded for now)
        rows[1].querySelector("b").innerText = formatPrice(0);

        // Total
        rows[2].querySelector("b").innerText = formatPrice(subtotal);
    };

    const parsePrice = (text) => {
        return Number(text.replace(/,/g, ""));
    };

    const formatPrice = (number) => {
        return number.toLocaleString("vi-VN");
    };

    const initCartFromBackend = () => {
        const el = document.getElementById("cart-init");
        if (!el || !el.value) return;

        let data;
        try {
            data = JSON.parse(el.value);
        } catch (e) {
            console.error("Invalid cart JSON from backend");
            return;
        }

        data.forEach((item) => {
            cart.set(item.productId, {
                productId: item.productId,
                name: item.name,
                price: item.price,
                qty: item.qty,
            });
        });

        renderCart();
    };

    initCartFromBackend();

    const serializeCart = () => {
        return Array.from(cart.values()).map((item) => ({
            productId: item.productId,
            qty: item.qty,
        }));
    };

    const syncCartToForm = () => {
        const input = document.getElementById("cart-submit");
        input.value = JSON.stringify(serializeCart());
    };

    document.getElementById("btn-pay").addEventListener("click", () => {
        syncCartToForm();
        document.getElementById("form-submit-cart").submit();
    });
    document.getElementById("btn-cancel").addEventListener("click", () => {
        resetCart();
    });

    const resetCart = () => {
        cart.clear();
        renderCart();
    };
});
