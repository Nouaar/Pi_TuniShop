document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".like-btn").forEach(button => {
        button.addEventListener("click", function () {
            let blogId = this.getAttribute("data-id");
            let likeCountSpan = this.querySelector(".like-count");

            fetch(`/blog/${blogId}/like`, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.likes !== undefined) {
                    likeCountSpan.textContent = data.likes;
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});
