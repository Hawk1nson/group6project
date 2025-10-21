
(function () {
  function text(el) {
    return (el.textContent || "").trim();
  }
  function num(v) {
    var n = parseFloat(v.replace(/[^0-9.\-]/g, ""));
    return isNaN(n) ? 0 : n;
  }
  function matches(hay, needle) {
    return hay.toLowerCase().indexOf(needle.toLowerCase()) !== -1;
  }

  function paginate(rows, page, perPage) {
    var start = (page - 1) * perPage;
    return rows.slice(start, start + perPage);
  }

  function renderBody(tbody, rows) {
    // Clear and append
    while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
    rows.forEach(function (tr) {
      tbody.appendChild(tr);
    });
  }

  function initTable(opts) {
    var table = document.getElementById(opts.tableId);
    var tbody = table.querySelector("tbody");
    var headers = table.querySelectorAll("thead th");
    var allRows = Array.prototype.slice.call(tbody.querySelectorAll("tr"));
    var state = {
      sortCol: null,
      dir: "asc",
      page: 1,
      perPage: opts.perPage || 10,
    };

    // Controls
    var q = document.querySelector(opts.selSearch);
    var selStat = document.querySelector(opts.selStatus);
    var yMin = document.querySelector(opts.selYearMin);
    var yMax = document.querySelector(opts.selYearMax);
    var pMin = document.querySelector(opts.selPriceMin);
    var pMax = document.querySelector(opts.selPriceMax);
    var perSel = document.querySelector(opts.selPerPage);
    var prevBtn = document.querySelector(opts.selPrev);
    var nextBtn = document.querySelector(opts.selNext);
    var metaLbl = document.querySelector(opts.selMeta);

    function currentFiltered() {
      var qv = q && q.value ? q.value.trim() : "";
      var sv = selStat && selStat.value ? selStat.value : "";
      var y1 = yMin && yMin.value ? parseInt(yMin.value, 10) : null;
      var y2 = yMax && yMax.value ? parseInt(yMax.value, 10) : null;
      var P1 = pMin && pMin.value ? parseFloat(pMin.value) : null;
      var P2 = pMax && pMax.value ? parseFloat(pMax.value) : null;

      return allRows.filter(function (tr) {
        var tds = tr.children;
        var year = parseInt(text(tds[1]) || "0", 10);
        var make = text(tds[2]);
        var model = text(tds[3]);
        var color = text(tds[4]);
        var price = num(text(tds[5]));
        var stat = text(tds[6]);
        var hay = [year, make, model, color].join(" ");

        if (qv && !matches(hay, qv)) return false;
        if (sv && stat !== sv) return false;
        if (y1 !== null && year < y1) return false;
        if (y2 !== null && year > y2) return false;
        if (P1 !== null && price < P1) return false;
        if (P2 !== null && price > P2) return false;
        return true;
      });
    }

    function sortRows(rows) {
      if (state.sortCol == null) return rows;
      var idx = state.sortCol;
      var dirMul = state.dir === "asc" ? 1 : -1;

      return rows.slice().sort(function (a, b) {
        var av = text(a.children[idx]);
        var bv = text(b.children[idx]);

        // numeric columns: 0=ID, 1=Year, 5=Price
        if (idx === 0 || idx === 1 || idx === 5) {
          return (num(av) - num(bv)) * dirMul;
        }
        return av.localeCompare(bv) * dirMul;
      });
    }

    function update() {
      var filtered = currentFiltered();
      var sorted = sortRows(filtered);

      var total = sorted.length;
      var pages = Math.max(1, Math.ceil(total / state.perPage));
      if (state.page > pages) state.page = pages;

      var pageRows = paginate(sorted, state.page, state.perPage);
      renderBody(tbody, pageRows);

      if (metaLbl)
        metaLbl.textContent =
          "Total: " + total + " • Page " + state.page + " of " + pages;
      if (prevBtn) prevBtn.disabled = state.page <= 1;
      if (nextBtn) nextBtn.disabled = state.page >= pages;
    }

    // Header click sorting
    headers.forEach(function (th, i) {
      if (!th.hasAttribute("data-sort")) return;
      th.style.cursor = "pointer";
      th.addEventListener("click", function () {
        if (state.sortCol === i) {
          state.dir = state.dir === "asc" ? "desc" : "asc";
        } else {
          state.sortCol = i;
          state.dir = "asc";
        }
        update();
      });
    });

    // Inputs
    [q, selStat, yMin, yMax, pMin, pMax].forEach(function (el) {
      if (!el) return;
      el.addEventListener("input", function () {
        state.page = 1;
        update();
      });
      el.addEventListener("change", function () {
        state.page = 1;
        update();
      });
    });

    if (perSel) {
      perSel.addEventListener("change", function () {
        state.perPage = parseInt(perSel.value, 10) || 10;
        state.page = 1;
        update();
      });
    }
    if (prevBtn)
      prevBtn.addEventListener("click", function (e) {
        e.preventDefault();
        state.page = Math.max(1, state.page - 1);
        update();
      });
    if (nextBtn)
      nextBtn.addEventListener("click", function (e) {
        e.preventDefault();
        state.page = state.page + 1;
        update();
      });

    // initial render
    update();
  }

  // expose
  window.SimpleTable = { init: initTable };
})();
