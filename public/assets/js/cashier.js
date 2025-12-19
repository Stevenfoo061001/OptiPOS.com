/* ================= GLOBAL STATE ================= */
let memberPoints = 0;
let memberDiscountValue = 0;
let selectedPaymentMethod = null;

let cashReceivedValue = 0;
let changeValue = 0;

/* ================= DOM READY ================= */
document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".payment-btn");
  const confirmBtn = document.getElementById("confirmPaymentBtn");

  buttons.forEach(btn => {
    btn.addEventListener("click", () => {
      buttons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      selectedPaymentMethod = btn.dataset.method;

      if (selectedPaymentMethod === "Cash") {
        openCashModal();
      } else {
        checkConfirmButton();
      }
    });
  });

  checkConfirmButton();
});

/* ================= CONFIRM BUTTON STATE ================= */
function checkConfirmButton() {
  const confirmBtn = document.getElementById("confirmPaymentBtn");
  let valid = selectedPaymentMethod !== null;

  if (selectedPaymentMethod === "Cash") {
    const total = parseFloat(
      document.getElementById("totalValue").textContent.replace("RM", "")
    );
    valid = cashReceivedValue >= total;
  }

  confirmBtn.disabled = !valid;
}

/* ================= MEMBER SEARCH ================= */
function searchMember() {
  const query = document.getElementById("memberInput").value.trim();
  if (!query) return;

  fetch("api/find_member.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ query })
  })
    .then(res => res.json())
    .then(data => {
      const result = document.getElementById("memberResult");
      const error = document.getElementById("memberError");

      if (!data.success) {
        memberPoints = 0;
        memberDiscountValue = 0;

        result.style.display = "none";
        error.style.display = "block";
        document.getElementById("memberDiscountRow").style.display = "none";

        recalculateTotal();
        return;
      }

      const MAX_POINTS = 1000;

    memberPoints = parseInt(data.member.points);

    // points that can actually be used
    const usablePoints = Math.min(memberPoints, MAX_POINTS);

    // discount based on usable points
    memberDiscountValue = usablePoints * 0.01;

    document.getElementById("memberName").textContent = data.member.name;
    document.getElementById("memberId").value = data.member.id;

    // show balance + usage clearly
    document.getElementById("memberPoints").textContent =
        `${memberPoints} pts (used ${usablePoints})`;

      document.getElementById("memberDiscount").textContent =
        `- RM ${memberDiscountValue.toFixed(2)}`;

      document.getElementById("memberDiscountRow").style.display = "flex";

      result.style.display = "block";
      error.style.display = "none";

      recalculateTotal();
    })
    .catch(() => {
      memberPoints = 0;
      memberDiscountValue = 0;

      document.getElementById("memberResult").style.display = "none";
      document.getElementById("memberError").style.display = "block";
      document.getElementById("memberDiscountRow").style.display = "none";

      recalculateTotal();
    });
}

/* ================= TOTAL CALCULATION ================= */
function recalculateTotal() {
  const subtotalEl = document.getElementById("subtotalValue");
  if (!subtotalEl) return;

  const subtotal = parseFloat(subtotalEl.dataset.value);
  const tax = subtotal * 0.06;

  let total = subtotal + tax - memberDiscountValue;
  if (total < 0) total = 0;

  document.getElementById("taxValue").textContent =
    `RM ${tax.toFixed(2)}`;

  document.getElementById("totalValue").textContent =
    `RM ${total.toFixed(2)}`;

  checkConfirmButton();
}

/* ================= CASH MODAL ================= */
function openCashModal() {
  document.getElementById("cashModal").style.display = "flex";
  document.getElementById("cashReceived").focus();
}

function closeCashModal() {
  document.getElementById("cashModal").style.display = "none";
}

function calculateChange() {
  cashReceivedValue = parseFloat(
    document.getElementById("cashReceived").value || 0
  );

  const total = parseFloat(
    document.getElementById("totalValue").textContent.replace("RM", "")
  );

  changeValue = cashReceivedValue - total;

  document.getElementById("changeAmount").textContent =
    changeValue >= 0 ? `RM ${changeValue.toFixed(2)}` : "RM 0.00";

  document.getElementById("confirmCashBtn").disabled = changeValue < 0;
}

function confirmCashPayment() {
  closeCashModal();
  checkConfirmButton();
}

/* ================= CONFIRM PAYMENT ================= */
document
  .getElementById("confirmPaymentBtn")
  .addEventListener("click", confirmPayment);

function confirmPayment() {
  if (!selectedPaymentMethod) return;

  fetch("api/save_transaction.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      payment: selectedPaymentMethod,
     memberId: document.getElementById("memberId").value || null,

      discount: memberDiscountValue,
      cashReceived:
        selectedPaymentMethod === "Cash" ? cashReceivedValue : 0,
      change:
        selectedPaymentMethod === "Cash" ? changeValue : 0
    })
  })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        alert(data.error || "Payment failed");
        return;
      }

      document.getElementById("modalPaymentMethod").textContent =
        selectedPaymentMethod;

      document.getElementById("modalTotalPaid").textContent =
        document.getElementById("totalValue").textContent;

      document.getElementById("paymentModal").style.display = "flex";
    });
}

/* ================= FINISH ================= */
function finishPayment() {
  window.location.href = "index.php?page=home";
}
