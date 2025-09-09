#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const srcRoot = path.join(root, 'src');

function listFiles(dir, exts = ['.js', '.jsx', '.ts', '.tsx']) {
  const res = [];
  const items = fs.readdirSync(dir, { withFileTypes: true });
  for (const it of items) {
    const full = path.join(dir, it.name);
    if (it.isDirectory()) {
      res.push(...listFiles(full, exts));
    } else if (exts.includes(path.extname(it.name))) {
      res.push(full);
    }
  }
  return res;
}

function pathMatchesCase(filePath) {
  // Walk path segments and check directory entries for exact casing
  const parts = path.resolve(filePath).split(path.sep);
  // On Windows, first element may be drive like C:
  let cur = parts[0] === '' ? path.sep : parts[0];
  for (let i = 1; i < parts.length; i++) {
    const seg = parts[i];
    try {
      const entries = fs.readdirSync(cur || path.sep);
      const found = entries.find(e => e === seg);
      if (!found) return false;
      cur = path.join(cur, seg);
    } catch (e) {
      return false;
    }
  }
  return true;
}

function resolveAlias(importPath, importerDir) {
  if (importPath.startsWith('@/')) {
    return path.join(srcRoot, importPath.slice(2));
  }
  if (importPath.startsWith('.')) {
    return path.resolve(importerDir, importPath);
  }
  return null; // external
}

function addExts(p) {
  const exts = ['', '.js', '.jsx', '.ts', '.tsx', '/index.js', '/index.ts', '/index.tsx', '/index.jsx'];
  for (const e of exts) {
    const candidate = p + e;
    if (fs.existsSync(candidate)) return candidate;
  }
  return null;
}

const files = listFiles(srcRoot);
const importRegex = /import\s+(?:[^;]+?)\s+from\s+["']([^"']+)["']/g;

const problems = [];
for (const f of files) {
  const txt = fs.readFileSync(f, 'utf8');
  let m;
  while ((m = importRegex.exec(txt)) !== null) {
    const importPath = m[1];
    const resolved = resolveAlias(importPath, path.dirname(f));
    if (!resolved) continue; // external package
    const candidate = addExts(resolved);
    if (!candidate) continue; // maybe missing file; skip
    if (!pathMatchesCase(candidate)) {
      problems.push({ importer: path.relative(root, f), importPath, resolved: path.relative(root, candidate) });
    }
  }
}

if (problems.length === 0) {
  console.log('No case-mismatch import paths found.');
  process.exit(0);
}

console.log('Found potential case-mismatch import paths (importer -> importPath -> resolved):');
for (const p of problems) {
  console.log(`- ${p.importer} -> ${p.importPath} -> ${p.resolved}`);
}
process.exit(1);
