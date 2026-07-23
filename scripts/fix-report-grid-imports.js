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
  if (!c.includes("applyReportGridData")) {
    continue;
  }

  const segments = file.replace(/\\/g, "/").split("/");
  const appIdx = segments.indexOf("app");
  const fileDirIdx = segments.length - 2;
  const levels = fileDirIdx - appIdx;
  const rel = "../".repeat(levels) + "utils/report-grid.helper";
  const imp = `import { applyReportGridData } from '${rel}';`;

  c = c.replace(
    /import \{ applyReportGridData \} from '[^']+';/,
    imp
  );
  fs.writeFileSync(file, c);
  console.log(levels, file);
}
