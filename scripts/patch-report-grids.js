const fs = require("fs");
const path = require("path");

function walk(dir, acc = []) {
  for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, ent.name);
    if (ent.isDirectory()) {
      walk(p, acc);
    } else {
      acc.push(p);
    }
  }
  return acc;
}

const root = path.join("src", "app", "admin-panel", "Reports");

for (const file of walk(root)) {
  if (!file.endsWith(".component.ts")) {
    continue;
  }
  let c = fs.readFileSync(file, "utf8");
  if (!c.includes("gridApi") || !c.includes("columnDefs") || !c.includes("this.rowData =")) {
    continue;
  }

  const segments = file.replace(/\\/g, "/").split("/");
  const reportsIdx = segments.indexOf("Reports");
  const levels = segments.length - reportsIdx - 2;
  const rel = "../".repeat(levels) + "utils/report-grid.helper";
  const imp = `import { applyReportGridData } from '${rel}';`;

  if (!c.includes("applyReportGridData")) {
    const end = c.indexOf(";", c.indexOf("import "));
    c = c.slice(0, end + 1) + "\n" + imp + c.slice(end + 1);
  }

  if (!c.includes("showReportGrid")) {
    if (c.match(/hideReport:\s*boolean\s*=\s*true;/)) {
      c = c.replace(/hideReport:\s*boolean\s*=\s*true;/, "hideReport: boolean = true;\n  showReportGrid = true;");
    } else if (c.match(/hideReport\s*=\s*true;/)) {
      c = c.replace(/hideReport\s*=\s*true;/, "hideReport = true;\n  showReportGrid = true;");
    } else {
      c = c.replace(/export class (\w+)/, "showReportGrid = true;\n\nexport class $1");
    }
  }

  c = c.replace(/this\.rowData\s*=\s*([^;]+);/g, (m, expr) => {
    const t = expr.trim();
    if (t === "[]") {
      return "this.rowData = [];\n          this.showReportGrid = false;";
    }
    if (t.includes("applyReportGridData")) {
      return m;
    }
    return `applyReportGridData(this, ${t});`;
  });

  fs.writeFileSync(file, c);
  console.log("TS", file);
}

for (const file of walk(root)) {
  if (!file.endsWith(".component.html")) {
    continue;
  }
  let h = fs.readFileSync(file, "utf8");
  if (!h.includes("<ag-grid-angular")) {
    continue;
  }
  if (h.includes("*ngIf=\"showReportGrid\"") || h.includes("*ngIf='showReportGrid'")) {
    continue;
  }
  h = h.replace(/<ag-grid-angular\s*/g, '<ag-grid-angular *ngIf="showReportGrid" ');
  fs.writeFileSync(file, h);
  console.log("HTML", file);
}
