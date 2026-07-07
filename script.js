const reviewButtons = document.querySelectorAll(".testimonial-btn");
const reviewCards = document.querySelectorAll(".testimonial-card");

reviewButtons.forEach((button, index) => {
  button.addEventListener("click", () => {
    reviewButtons.forEach((item) => item.classList.remove("active"));
    reviewCards.forEach((card) => card.classList.remove("active"));

    button.classList.add("active");
    if (reviewCards[index]) {
      reviewCards[index].classList.add("active");
    }
  });
});

const subscribeForm = document.querySelector(".subscribe-form");

if (subscribeForm) {
  subscribeForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const emailInput = subscribeForm.querySelector('input[type="email"]');

    if (!emailInput || !emailInput.value.trim()) {
      emailInput?.focus();
      return;
    }

    alert("Thanks! Your email has been received.");
    subscribeForm.reset();
  });
}
