import testmu, { resolveVar, setVar } from '@testmuai/playwright-bindings';
import { expect } from '@playwright/test';

testmu.configure({
  "build": "163fb262-ace1-4708-b42e-446c1428d080",
  "name": "Save a Webpage Using Wallabag Bookmarklet",
  "tcId": "TC-175",
  "network": (process.env.NETWORK ?? "false").toLowerCase() === "true",
  "autoHealVersion": "AH2",
  "defaultActionTimeoutMs": 10000,
  "defaultNavigationTimeoutMs": 60000,
  "kaneRunV4": true
});

testmu.test("Save a Webpage Using Wallabag Bookmarklet", async (page) => {
  let element_0, element_1, element_2;
  await testmu.step("Navigate to http://127.0.0.1:8000/login", { instructionId: "deec605f-d7d8-453d-80f2-56c42afe7ac9" }, async () => {
    await page.goto("http://127.0.0.1:8000/login");
  });
  
  await testmu.step("Waiting for the login fields to be filled", { instructionId: "084d8867-cbfd-4c60-86b3-38a5793ec543" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Type \"wallabag\" into \"Username\"", { instructionId: "db5fd634-c118-480a-b4d8-6f3a172c25a2" }, async () => {
    element_0 = page.locator("internal:role=textbox[name=\"Username\"i]");
    await element_0.click();
    await element_0.fill("wallabag");
  });
  
  await testmu.step("Type \"wallabag\" into \"Password\"", { instructionId: "15106e7c-0b25-416d-87f2-5a31ed852676" }, async () => {
    element_1 = page.locator("internal:role=textbox[name=\"Password\"i]");
    await element_1.click();
    await element_1.fill("wallabag");
  });
  
  await testmu.step("Click \"LOG IN send\"", { instructionId: "218910f0-02c1-4655-bfda-e738361cefbf" }, async () => {
    element_2 = page.locator("internal:role=button[name=\"Log in send\"i]");
    await element_2.click();
  });
  
  await testmu.step("Waiting 300 ms after successful login navigation", { instructionId: "43a5f1e2-b62f-46f4-8213-ce1518361072" }, async () => {
    await page.waitForTimeout(300);
  });
  
  await testmu.step("Waiting 1 second for the Quickstart page to finish loading", { instructionId: "9dd76f3a-4493-40b9-988b-cb08bfd28c32" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Reading the current page URL", { instructionId: "c6b81df3-c52c-4ecc-8a6a-054f95a9a47b" }, async () => {
    setVar("url_not_login_check", page.url());
  });
  
  await testmu.step("Assertion check", { instructionId: "b55880ca-2951-4c2b-8550-da1d3ebee4ce" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "The current page URL does not contain \"/login\".", "passed": true, "operator": "not_contains", "transforms": [], "json_path": null, "expected": "/login", "extracted_value": "{{url_not_login_check}}", "store_key": "url_not_login_check", "variable_refs": {"{{url_not_login_check}}": "http://127.0.0.1:8000/quickstart"}}], "sub_checks": [{"description": "The current page URL does not contain \"/login\".", "store_key": "url_not_login_check", "expected_value": "/login", "extracted_value": "{{url_not_login_check}}", "operator": "not_contains", "transforms": []}], "composite_operator": "and", "claim": "the URL does not contain \"/login\""});
  });
  
  await testmu.step("Navigate to http://127.0.0.1:8000/bookmarklet?url=https://example.com/", { instructionId: "945f1b34-825c-470e-a4a6-53ce33a4a762" }, async () => {
    await page.goto("http://127.0.0.1:8000/bookmarklet?url=https://example.com/");
  });
  
  await testmu.step("Waiting for unread entries page to finish loading", { instructionId: "ebb36d04-cb74-4313-b974-d4ffc8a23b91" }, async () => {
    await page.waitForTimeout(1000);
  });
  
  await testmu.step("Checking for the example.com entry in the unread list", async () => {
    await page.waitForLoadState("domcontentloaded", { timeout: 5000 });
    setVar("unread_list_example_com_entry", await testmu.textualAnalyzer(page, { wrappedJs: "(els) => {\n  const __m = {47: 0};\n  const el = (i) => els[__m[i]];\n  const __v = (el(47).textContent.trim() === 'example.com');\n  return (typeof __v === 'boolean' ? String(__v) : __v);\n}", locators: ["internal:role=link[name=\"example.com\"i] >> nth=0"], query: "whether the unread list contains an entry titled example.com", expectedValue: "true", needsUnitConversion: false, operator: "equals", transforms: [], condition: "The unread list contains an entry titled \"example.com\"." }));
  });
  
  await testmu.step("Assertion check", { instructionId: "7ed7ed92-dff5-4b64-be28-a8a8e25a5373" }, async () => {
    await testmu.verifyAssertion(page, "Assertion check", {"operator": ["equals"], "assertion_operands": [], "left_operand": null, "right_operand": null, "operands": [], "sub_results": [{"description": "The unread list contains an entry titled \"example.com\".", "passed": true, "operator": "equals", "transforms": [], "json_path": null, "expected": "true", "extracted_value": "{{unread_list_example_com_entry}}", "store_key": "unread_list_example_com_entry", "variable_refs": {"{{unread_list_example_com_entry}}": "true"}}], "sub_checks": [{"description": "The unread list contains an entry titled \"example.com\".", "store_key": "unread_list_example_com_entry", "expected_value": "true", "extracted_value": "{{unread_list_example_com_entry}}", "operator": "equals", "transforms": []}], "composite_operator": "and", "claim": "the unread list contains an entry titled \"example.com\""});
  });
});

testmu.run();