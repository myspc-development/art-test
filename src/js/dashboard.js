import domReady from "@wordpress/dom-ready";

domReady(() => {
    document.querySelectorAll(".ap-card").forEach((card) => {
        if (!card.id) {
            return;
        }
        const toggle = document.createElement("button");
        toggle.className = "dashicons dashicons-arrow-down";
        toggle.setAttribute("aria-label", "Collapse widget");
        toggle.onclick = () => {
            card.classList.toggle("is-collapsed");
            try {
                localStorage.setItem(
                    card.id + ":collapsed",
                    card.classList.contains("is-collapsed"),
                );
            } catch {
                /* noop */
            }
        };
        card.prepend(toggle);
        try {
            if (localStorage.getItem(card.id + ":collapsed") === "true") {
                card.classList.add("is-collapsed");
            }
        } catch {
            /* noop */
        }
    });
});
