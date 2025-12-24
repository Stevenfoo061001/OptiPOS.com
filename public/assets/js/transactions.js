let transactions = [];

const itemsBox   = document.getElementById("receiptItems");
const subtotalEl = document.getElementById("rSubtotal");
const taxEl      = document.getElementById("rTax");
const discountEl = document.getElementById("rDiscount");
const totalEl    = document.getElementById("rTotal");
const paymentEl  = document.getElementById("rPayment");
const memberEl = document.getElementById("rMember");


document.addEventListener("DOMContentLoaded", () => {
  loadTransactions();

  document.getElementById("transactionSearch")
    .addEventListener("input", filterTransactions);
});

function loadTransactions() {
  const list = document.getElementById("transactionList");

  list.innerHTML = "<p class='empty'>Loading...</p>";

  fetch(`${BASE_URL}/api/transactions.php`)
    .then(res => {
      if (!res.ok) {
        throw new Error("Request failed");
      }
      return res.json();
    })
    .then(data => {
      transactions = data;

      if (transactions.length === 0) {
        list.innerHTML = "<p class='empty'>No transactions found</p>";
        return;
      }

      // 有数据才 render
      renderTransactionList();
      renderReceipt(0);
    })
    .catch(err => {
      console.error(err);

      list.innerHTML =
        "<p class='empty'>Failed to load transactions</p>";
    });
}


function renderTransactionList() {
  const list = document.getElementById("transactionList");

  list.innerHTML = "";

  transactions.forEach((trx, index) => {
    const div = document.createElement("div");
    div.className = "transaction-item" + (index === 0 ? " active" : "");
    div.dataset.index = index;

    div.innerHTML = `
      <strong>${trx.transactionid}</strong>
      <div class="trx-date">${trx.payment_date} ${trx.payment_time}</div>
    `;

    div.addEventListener("click", () => {
      document.querySelectorAll(".transaction-item")
        .forEach(i => i.classList.remove("active"));

      div.classList.add("active");
      renderReceipt(index);
    });

    list.appendChild(div);
  });
}

function renderReceipt(index) {
  const trx = transactions[index];
  if (!trx) return;

  itemsBox.innerHTML = "Loading...";

  fetch(`${BASE_URL}/api/get_receipt_items.php?orderid=${trx.orderid}`)
    .then(res => res.json())
    .then(items => {

      itemsBox.innerHTML = "";

      if (items.length === 0) {
        itemsBox.innerHTML = "<p>No items found</p>";
      }

      items.forEach(item => {
        const lineTotal = item.unitprice * item.quantity;

        const row = document.createElement("div");
        row.className = "receipt-item";
        row.innerHTML = `
          <span>${item.name} x ${item.quantity}</span>
          <span>RM ${lineTotal.toFixed(2)}</span>
        `;
        itemsBox.appendChild(row);
      });

      subtotalEl.textContent = `RM ${parseFloat(trx.subtotal).toFixed(2)}`;
      taxEl.textContent      = `RM ${parseFloat(trx.tax).toFixed(2)}`;
      discountEl.textContent = `- RM ${parseFloat(trx.discount).toFixed(2)}`;
      totalEl.textContent    = `RM ${parseFloat(trx.grandtotal).toFixed(2)}`;
      paymentEl.textContent  = trx.paymentmethod;
      if (trx.member_name) {
        memberEl.textContent = `${trx.member_name} (${trx.member_points} pts)`;
    } else {
        memberEl.textContent = "Walk-in";
}

    });
}

function filterTransactions(e) {
  const keyword = e.target.value.toLowerCase();

  document.querySelectorAll(".transaction-item").forEach(item => {
    const trx = transactions[item.dataset.index];

    const match =
      trx.transactionid.toLowerCase().includes(keyword) ||
      trx.paymentmethod.toLowerCase().includes(keyword) ||
      trx.userid.toLowerCase().includes(keyword);

    item.style.display = match ? "block" : "none";
  });
}
