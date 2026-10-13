import testmu, { resolveVar, setVar } from '@testmuai/playwright-bindings';
import { expect } from '@playwright/test';

testmu.configure({
  "build": "206856ae-c129-422e-9b14-f14b1650a0f5",
  "name": "Test Login and Logout Functionality",
  "tcId": "TC-174",
  "network": (process.env.NETWORK ?? "false").toLowerCase() === "true",
  "autoHealVersion": "AH2",
  "defaultActionTimeoutMs": 10000,
  "defaultNavigationTimeoutMs": 60000,
  "kaneRunV4": true
});

testmu.test("Test Login and Logout Functionality", async (page) => {
  let element_0, element_1, element_3, element_2, element_5;
  await testmu.step("Navigate to http://127.0.0.1:8000/login", { instructionId: "765419aa-ab56-46d1-b604-b1911a57560e" }, async () => {
    await page.goto("http://127.0.0.1:8000/login");
  });
  
  await testmu.step("Waiting for queued login steps to run", { instructionId: "d4ad1e60-125d-4447-b594-84ccb79e1744" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Type \"wallabag\" into \"Username\"", { instructionId: "98e2b80e-c2fc-4b41-8850-e84956c8cb38" }, async () => {
    element_0 = page.locator("internal:role=textbox[name=\"Username\"i]");
    await element_0.click();
    await element_0.fill("wallabag");
  });
  
  await testmu.step("Type \"not-the-password\" into \"Password\"", { instructionId: "adca8f2f-3bab-4f08-b957-801779d184ae" }, async () => {
    element_1 = page.locator("internal:role=textbox[name=\"Password\"i]");
    await element_1.click();
    await element_1.fill("not-the-password");
  });
  
  await testmu.step("Click \"LOG IN send\"", { instructionId: "c7aa365e-7632-46fc-be59-bab9d9109cd3" }, async () => {
    element_3 = page.locator("internal:role=button[name=\"Log in send\"i]");
    await element_3.click();
  });
  
  await testmu.step("Waiting 1 second after submitting the login form", { instructionId: "7d9c6572-7001-4075-bc47-cffdb18adf56" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Waiting 1000 ms for the login page to finish loading", { instructionId: "4f708c56-2b6a-4c6a-ad2f-b58f3b8bce98" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Reading the current page URL", { instructionId: "20512eb8-b3a9-44ce-a9c1-211999a7a80a" }, async () => {
    setVar("login_url_check", page.url());
  });
  
  await testmu.step("Checking whether the Log in button is visible", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("login_button_visible", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {16: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (!!el(16) && !el(16).disabled);\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=button[name=\"Log in send\"i]"], query: "whether the Log in button is present and enabled on the login form", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "the \"Log in\" button is still shown." }));
  });
  
  await testmu.step("Assertion check", { instructionId: "e15cb646-92dd-4957-b330-8e860dceb86b" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "the URL contains \"/login\".", "passed": true, "operator": "contains", "transforms": [], "json_path": null, "expected": "/login", "extracted_value": "{{login_url_check}}", "store_key": "login_url_check", "variable_refs": {"{{login_url_check}}": "http://127.0.0.1:8000/login"}}, {"description": "the \"Log in\" button is still shown.", "passed": true, "operator": "equals", "transforms": [], "json_path": null, "expected": "true", "extracted_value": "{{login_button_visible}}", "store_key": "login_button_visible", "variable_refs": {"{{login_button_visible}}": "true"}}], "sub_checks": [{"description": "the URL contains \"/login\".", "store_key": "login_url_check", "expected_value": "/login", "extracted_value": "{{login_url_check}}", "operator": "contains", "transforms": []}, {"description": "the \"Log in\" button is still shown.", "store_key": "login_button_visible", "expected_value": "true", "extracted_value": "{{login_button_visible}}", "operator": "equals", "transforms": []}], "composite_operator": "and", "claim": "the URL contains \"/login\" and the \"Log in\" button is still shown."});
  });
  
  await testmu.step("Navigate to http://127.0.0.1:8000/login", { instructionId: "be0a1ce1-a800-4ffb-a0bb-3eabfd6c2b4f" }, async () => {
    await page.goto("http://127.0.0.1:8000/login");
  });
  
  await testmu.step("Waiting for the login plan to run", { instructionId: "e63f7502-1c78-4205-834d-4f28fe8599b7" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Type \"wallabag\" into \"Password\"", { instructionId: "66222d13-0955-4797-95d6-65383b514158" }, async () => {
    element_2 = page.locator("internal:role=textbox[name=\"Password\"i]");
    await element_2.click();
    await element_2.fill("wallabag");
  });
  
  await testmu.step("Click \"LOG IN send\"", { instructionId: "10ee707f-c2ad-4597-b3f0-241b387f1e55" }, async () => {
    element_5 = page.locator("internal:role=button[name=\"Log in send\"i]");
    await element_5.click();
  });
  
  await testmu.step("Waiting for Wallabag unread entries to finish loading", { instructionId: "effdbec5-1b37-4996-861c-5947c53168d5" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Reading the current page URL", { instructionId: "ccf54a56-b265-456f-9480-3c66ae66b7e8" }, async () => {
    setVar("url_not_login_check", page.url());
  });
  
  await testmu.step("Assertion check", { instructionId: "b9edcaaf-8b3b-476a-bc3c-34cb5aa4c6ae" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "The current page URL does not contain \"/login\".", "passed": true, "operator": "not_contains", "transforms": [], "json_path": null, "expected": "/login", "extracted_value": "{{url_not_login_check}}", "store_key": "url_not_login_check", "variable_refs": {"{{url_not_login_check}}": "http://127.0.0.1:8000/"}}], "sub_checks": [{"description": "The current page URL does not contain \"/login\".", "store_key": "url_not_login_check", "expected_value": "/login", "extracted_value": "{{url_not_login_check}}", "operator": "not_contains", "transforms": []}], "composite_operator": "and", "claim": "the URL does not contain \"/login\""});
  });
  
  await testmu.step("Navigate to http://127.0.0.1:8000/logout", { instructionId: "7329c8f2-87aa-4144-a2d4-efce4fdfebea" }, async () => {
    await page.goto("http://127.0.0.1:8000/logout");
  });
  
  await testmu.step("Waiting for the login page to finish loading", { instructionId: "cd143eea-5f50-43b2-b91a-c0fbe0012ee5" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Reading the current page URL", { instructionId: "300011b7-035c-4f1f-b4ed-39f161b5ae54" }, async () => {
    setVar("url_contains_login", page.url());
  });
  
  await testmu.step("Checking whether the Log in button is visible", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("log_in_button_visible", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {16: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (!!(el(16) && el(16).getClientRects().length && getComputedStyle(el(16)).visibility !== 'hidden' && getComputedStyle(el(16)).display !== 'none'));\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=button[name=\"Log in send\"i]"], query: "whether the Log in button is visible", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "The \"Log in\" button is shown." }));
  });
  
  await testmu.step("Assertion check", { instructionId: "1d74601d-4449-4f65-82a0-98a8b8193349" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "The URL contains \"/login\".", "passed": true, "operator": "contains", "transforms": [], "json_path": null, "expected": "/login", "extracted_value": "{{url_contains_login}}", "store_key": "url_contains_login", "variable_refs": {"{{url_contains_login}}": "http://127.0.0.1:8000/login"}}, {"description": "The \"Log in\" button is shown.", "passed": true, "operator": "equals", "transforms": [], "json_path": null, "expected": "true", "extracted_value": "{{log_in_button_visible}}", "store_key": "log_in_button_visible", "variable_refs": {"{{log_in_button_visible}}": "true"}}], "sub_checks": [{"description": "The URL contains \"/login\".", "store_key": "url_contains_login", "expected_value": "/login", "extracted_value": "{{url_contains_login}}", "operator": "contains", "transforms": []}, {"description": "The \"Log in\" button is shown.", "store_key": "log_in_button_visible", "expected_value": "true", "extracted_value": "{{log_in_button_visible}}", "operator": "equals", "transforms": []}], "composite_operator": "and", "claim": "Verify the URL contains \"/login\" and the \"Log in\" button is shown."});
  });
});

testmu.run();