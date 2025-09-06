#!/usr/bin/env node
import { spawnSync } from "child_process";
import { readdirSync, statSync } from "fs";
import { join, extname } from "path";
const roots = ["artpulse.php","includes","src","widgets","templates"];
const batch = 120;
const php = process.env.PHP_BINARY || "php";
const binVendor = "vendor/bin/phpcs";
const useComposer = !statSafe(binVendor);
const base = useComposer ? ["composer","exec","--","phpcs"] : [php, "-d", "memory_limit=1024M", binVendor];
function statSafe(p){ try{ return statSync(p); }catch{ return null; } }
function listPhp(dir){ const out=[]; function w(p){ const s=statSafe(p); if(!s) return; if(s.isFile()) { if(extname(p).toLowerCase()===".php") out.push(p); return; } if(s.isDirectory()) for(const f of readdirSync(p)) w(join(p,f)); } w(dir); return out; }
let files=[]; for(const r of roots) files=files.concat(listPhp(r));
if(files.length===0){ console.log("No PHP files found in source."); process.exit(0); }
let code=0;
for(let i=0;i<files.length;i+=batch){
  const chunk=files.slice(i,i+batch);
  const args = base.slice(1).concat(["--standard=phpcs.xml.dist","--report=summary"]).concat(chunk);
  const cmd = base[0];
  const res=spawnSync(cmd, args, { stdio:"inherit" });
  if(res.status!==0) code = res.status;
}
process.exit(code);
