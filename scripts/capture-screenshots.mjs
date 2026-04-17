import { chromium } from "playwright";
import fs from "node:fs";
import path from "node:path";

const baseUrl = process.env.SCREENSHOT_BASE_URL || "http://127.0.0.1:8000";
const crmEmail = process.env.CRM_EMAIL || "admincrm@vertue.com";
const crmPassword = process.env.CRM_PASSWORD || "Vertue2026";
const portalEmail = process.env.PORTAL_EMAIL || "priya@example.com";
const portalPassword = process.env.PORTAL_PASSWORD || "Student123!";
const outDir = path.resolve(process.cwd(), "outputs", "screenshots");
fs.mkdirSync(outDir, { recursive: true });

const pages = [
  { name: "crm-login", url: `${baseUrl}/login`, requiresAuth: false },
  { name: "portal-login", url: `${baseUrl}/portal/login`, requiresAuth: false },
  { name: "crm-dashboard", url: `${baseUrl}/dashboard`, requiresAuth: "crm" },
  { name: "crm-dashboard-mobile", url: `${baseUrl}/dashboard`, requiresAuth: "crmMobile" },
  { name: "students-list", url: `${baseUrl}/students`, requiresAuth: "crm" },
  { name: "students-list-mobile", url: `${baseUrl}/students`, requiresAuth: "crmMobile" },
  { name: "applications-list", url: `${baseUrl}/applications`, requiresAuth: "crm" },
  { name: "agents-roles", url: `${baseUrl}/agents`, requiresAuth: "crm" },
  { name: "portal-dashboard", url: `${baseUrl}/portal/dashboard`, requiresAuth: "portal" },
  { name: "portal-universities", url: `${baseUrl}/portal/universities`, requiresAuth: "portal" },
  { name: "portal-documents", url: `${baseUrl}/portal/documents`, requiresAuth: "portal" },
];

const browser = await chromium.launch({ headless: true });
const crmContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const crmMobileContext = await browser.newContext({ viewport: { width: 430, height: 932 } });
const portalContext = await browser.newContext({ viewport: { width: 430, height: 932 } });
const publicContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });

async function fillIfExists(page, selector, value) {
  const node = page.locator(selector).first();
  if ((await node.count()) > 0) {
    await node.fill(value);
    return true;
  }
  return false;
}

async function loginCrm() {
  const page = await crmContext.newPage();
  await page.goto(`${baseUrl}/login`, { waitUntil: "networkidle", timeout: 30000 });
  await fillIfExists(page, 'input[name="email"]', crmEmail);
  await fillIfExists(page, 'input[name="login"]', crmEmail);
  await fillIfExists(page, 'input[type="password"]', crmPassword);
  const submit = page.locator('button[type="submit"], input[type="submit"]').first();
  if ((await submit.count()) > 0) {
    await submit.click();
    await page.waitForTimeout(1200);
  }
  await page.close();
}

async function loginCrmMobile() {
  const page = await crmMobileContext.newPage();
  await page.goto(`${baseUrl}/login`, { waitUntil: "networkidle", timeout: 30000 });
  await fillIfExists(page, 'input[name="email"]', crmEmail);
  await fillIfExists(page, 'input[name="login"]', crmEmail);
  await fillIfExists(page, 'input[type="password"]', crmPassword);
  const submit = page.locator('button[type="submit"], input[type="submit"]').first();
  if ((await submit.count()) > 0) {
    await submit.click();
    await page.waitForTimeout(1200);
  }
  await page.close();
}

async function loginPortal() {
  const page = await portalContext.newPage();
  await page.goto(`${baseUrl}/portal/login`, { waitUntil: "networkidle", timeout: 30000 });
  await fillIfExists(page, 'input[name="email"]', portalEmail);
  await fillIfExists(page, 'input[name="login"]', portalEmail);
  await fillIfExists(page, 'input[type="password"]', portalPassword);
  const submit = page.locator('button[type="submit"], input[type="submit"]').first();
  if ((await submit.count()) > 0) {
    await submit.click();
    await page.waitForTimeout(1200);
  }
  await page.close();
}

await loginCrm();
await loginCrmMobile();
await loginPortal();

for (const item of pages) {
  try {
    let page;
    if (item.requiresAuth === "crm") {
      page = await crmContext.newPage();
    } else if (item.requiresAuth === "crmMobile") {
      page = await crmMobileContext.newPage();
    } else if (item.requiresAuth === "portal") {
      page = await portalContext.newPage();
    } else {
      page = await publicContext.newPage();
    }
    await page.goto(item.url, { waitUntil: "networkidle", timeout: 30000 });
    await page.screenshot({
      path: path.join(outDir, `${item.name}.png`),
      fullPage: true,
    });
    await page.close();
    console.log(`ok: ${item.name}`);
  } catch (error) {
    console.log(`failed: ${item.name} -> ${error.message}`);
  }
}

await crmContext.close();
await crmMobileContext.close();
await portalContext.close();
await publicContext.close();
await browser.close();
console.log(`saved: ${outDir}`);
