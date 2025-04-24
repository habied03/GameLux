const nominalList = {
  ml: [
    { nominal: "5 Diamonds", harga: 1600, sku: "ML5" }, 
    { nominal: "12 Diamonds", harga: 3600, sku: "ML12" },
    { nominal: "50 Diamonds", harga: 14600, sku: "ML50" },
    { nominal: "100 Diamonds", harga: 28300, sku: "ML100" },
    { nominal: "170 Diamonds", harga: 48000, sku: "ML170" },
    { nominal: "222 Diamonds", harga: 62400, sku: "ML222" },
    { nominal: "370 Diamonds", harga: 104000, sku: "ML370" },
    { nominal: "518 Diamonds", harga: 145700, sku: "ML518" },
    { nominal: "966 Diamonds", harga: 260000, sku: "ML966" }
  ],
  ff: [
    { nominal: "25 Diamonds", harga: 4300, sku: "FF25" },
    { nominal: "55 Diamonds", harga: 8000, sku: "FF55" },
    { nominal: "70 Diamonds", harga: 9600, sku: "FF70" },
    { nominal: "100 Diamonds", harga: 14700, sku: "FF100" },
    { nominal: "140 Diamonds", harga: 20000, sku: "FF140" },
    { nominal: "210 Diamonds", harga: 28100, sku: "FF210" },
    { nominal: "350 Diamonds", harga: 47100, sku: "FF350" },
    { nominal: "425 Diamonds", harga: 53000, sku: "FF425" },
    { nominal: "512 Diamonds", harga: 64000, sku: "FF512" },
    { nominal: "710 Diamonds", harga: 87500, sku: "FF710" }
  ],
  pubg: [
    { nominal: "60 UC", harga: 15300, sku: "PUBG60" },
    { nominal: "180 UC", harga: 46200, sku: "PUBG180" },
    { nominal: "325 UC", harga: 77400, sku: "PUBG325" },
    { nominal: "510 UC", harga: 118000, sku: "PUBG510" },
    { nominal: "720 UC", harga: 170100, sku: "PUBG720" },
    { nominal: "1000 UC", harga: 248000, sku: "PUBG1000" },
    { nominal: "1250 UC", harga: 262115, sku: "PUBG1250" },
    { nominal: "1500 UC", harga: 330456, sku: "PUBG1500" }
  ]
};

function selectGame(game) {
  document.getElementById("game").value = game;
  updateNominalOptions(game);
}

document.getElementById("game").addEventListener("change", function () {
  const selectedGame = this.value;
  updateNominalOptions(selectedGame);
});

function updateNominalOptions(game) {
  const nominalSelect = document.getElementById("nominal");
  nominalSelect.innerHTML = '<option value="">Pilih Nominal</option>';

  if (nominalList[game]) {
    nominalList[game].forEach(item => {
      const option = document.createElement("option");
      option.value = item.nominal;
      option.textContent = `${item.nominal} - Rp ${item.harga.toLocaleString("id-ID")}`;
      option.setAttribute("data-harga", item.harga);
      option.setAttribute("data-sku", item.sku);
      nominalSelect.appendChild(option);
    });
  }
}

document.getElementById("topupForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const game = document.getElementById("game").value;
  const gameId = document.getElementById("gameId").value.trim();
  const nominal = document.getElementById("nominal").value;
  const payment = document.getElementById("payment").value;
  const selectedOption = document.getElementById("nominal").selectedOptions[0];
  const harga = selectedOption.getAttribute("data-harga");
  const sku = selectedOption.getAttribute("data-sku");

  if (!game || !gameId || !nominal || !payment || !harga || !sku) {
    alert("Harap lengkapi semua data sebelum melanjutkan.");
    return;
  }

  // Kirim ke backend (topup.php)
  fetch('topup.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ game, gameId, nominal, payment, harga, sku })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success && data.redirect_url) {
      window.location.href = data.redirect_url; // ke Tripay
    } else {
      alert('Gagal membuat transaksi: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(err => {
    console.error(err);
    alert('Terjadi kesalahan saat menghubungi server.');
  });
});