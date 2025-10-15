function resetSelect(selectorId) {
    let selector = document.getElementById(selectorId);
    selector.selectedIndex = 0; // This sets the first option as selected

    selector.closest("div.form-group").classList.add("d-none");
    selector.removeAttribute("required");
}

function handleApplicationForm(event) {
    let mainCategorySelect = document.getElementById("typeSelect");
    let categoryId = mainCategorySelect.value;
    let category = findCategoryById(categoryId);
    resetSelect("subCategiresSelect");
    resetSelect("bundleSelect");
    resetSelect("webinarSelect");
    resetSelect("additionBundleSelect");
    showBookSeatButton(false);

    document.getElementById("addition_section").classList.add("d-none");
    if (category?.active_sub_categories?.length > 0) {
        displaySubCategories(category?.active_sub_categories);
    } else if (category?.active_bundles?.length > 0) {
        dispayProgramsOfCategory(category);
    } else if (category?.active_webinars?.length > 0) {
        dispayCoursesOfCategory(category);
    }

    CertificateSectionToggle();
    coursesToggle();
}

function handleSubCategoryChange(event) {
    resetSelect("bundleSelect");
    resetSelect("webinarSelect");
    resetSelect("additionBundleSelect");
    showBookSeatButton(false);
    document.getElementById("addition_section").classList.add("d-none");
    let subCategiresSelect = document.getElementById("subCategiresSelect");
    let categoryId = subCategiresSelect.value;
    // let categoryId = event.target.value;

    let category = findCategoryById(categoryId);

    if (category && category?.active_bundles?.length > 0) {
        dispayProgramsOfCategory(category);
    } else if (category && category?.active_webinars?.length > 0) {
        dispayCoursesOfCategory(category);
    }

    coursesToggle();
    CertificateSectionToggle();
}

function handleBundleChange(event) {
    let bundleSelect = document.getElementById("bundleSelect");
    let bundleId = bundleSelect.value;
    let bundle = getBundleById(bundleId);
    let addition_section = document.getElementById("addition_section");
    let additionBundleSelect = document.getElementById("additionBundleSelect");
    let additionInputs = document.querySelectorAll(
        "input[name='want_addition']"
    );

    bundleAddtionSelectToggle();
    if (bundle && bundle?.addition_bundles?.length > 0) {
        addition_section.classList.remove("d-none");

        additionInputs.forEach(function (element) {
            element.setAttribute("required", true);
        });
    } else {
        addition_section.classList.add("d-none");
        additionBundleSelect.closest("div").classList.add("d-none");
        additionBundleSelect.removeAttribute("name");
        additionBundleSelect.removeAttribute("required");

        additionInputs.forEach(function (element) {
            element.removeAttribute("required");
        });
    }
}

function displaySubCategories(subCategories) {
    let subCategiresSelect = document.getElementById("subCategiresSelect");
    if (subCategories.length > 0) {
        let content = `<option selected hidden value="">اختر التخصص الذي تريد دراسته في
                                            اكاديمية انس للفنون </option>`;

        subCategories.forEach((category) => {
            let isSelected =
                category.id == oldValues?.sub_category_id ? "selected" : "";
            content += `<option value="${category.id}" ${isSelected}>${category.title}</option>`;
        });

        subCategiresSelect.innerHTML = content;
        subCategiresSelect.setAttribute("required", "true");
        subCategiresSelect.closest("div.form-group").classList.remove("d-none");
        handleSubCategoryChange();
    } else {
        subCategiresSelect.innerHTML = `<option selected hidden value="">لا توجد تخصصات في هذا التصنيف</option>`;
        subCategiresSelect.removeAttribute("required");
        subCategiresSelect.closest("div.form-group").classList.add("d-none");
    }
}

function dispayProgramsOfCategory(category) {
    let bundleSelect = document.getElementById("bundleSelect");
    if (category?.active_bundles?.length > 0) {
        let content = `<option selected hidden value="">اختر البرنامج الذي تريد دراسته في
                                        اكاديمية انس للفنون </option>`;

        category.active_bundles.forEach((bundle) => {
            let isSelected =
                bundle.id == oldValues?.bundle_id ? "selected" : "";
            content += `<option value="${bundle.id}" ${isSelected}
                        has_certificate="${bundle.has_certificate}"
                        early_enroll="${bundle.early_enroll}"
                        >${bundle.title}
                        </option>`;
        });

        bundleSelect.innerHTML = content;
        bundleSelect.setAttribute("required", "true");
        bundleSelect.closest("div.form-group").classList.remove("d-none");
        handleBundleChange();
        showBookSeatButton(true);
    } else {
        bundleSelect.innerHTML = `<option selected hidden value="">لا توجد برامج في هذا التصنيف</option>`;
        bundleSelect.removeAttribute("required");
        bundleSelect.closest("div.form-group").classList.add("d-none");
        showBookSeatButton(false);
    }
}

function showBookSeatButton(flag) {
    let formButton = document.getElementById("form_button");
    let formSubmit = document.getElementById("formSubmit");

    if (formSubmit && formButton) {
        if (flag) {
            formSubmit.innerHTML = " حجز مقعد";
            formButton.classList.remove("d-none");
        } else {
            formButton.classList.add("d-none");
            formSubmit.innerHTML = "تسجيل";
        }
    }
}

function dispayCoursesOfCategory(category) {
    let webinarSelect = document.getElementById("webinarSelect");

    if (category?.active_webinars?.length > 0) {
        let content = `<option selected hidden value="">اختر البرنامج الذي تريد دراسته في
                                        اكاديمية انس للفنون </option>`;

        category.active_webinars.forEach((webinar) => {
            let isSelected =
                webinar.id == oldValues?.webinar_id ? "selected" : "";
            content += `<option value="${webinar.id}" ${isSelected}>${webinar.title}</option>`;
        });

        webinarSelect.innerHTML = content;
        webinarSelect.setAttribute("required", "true");
        webinarSelect.closest("div.form-group").classList.remove("d-none");
    } else {
        webinarSelect.innerHTML = `<option selected hidden value="">لا توجد برامج في هذا التصنيف</option>`;
        webinarSelect.removeAttribute("required");
        webinarSelect.closest("div.form-group").classList.add("d-none");
    }
}

function getCategorySubCategories(mainCategoryId) {
    let category = categories.find((category) => category.id == mainCategoryId);
    return category?.active_sub_categories ?? [];
}

function findCategoryById(id) {
    // Iterate over the categories and look for a direct match or a subcategory match
    return categories.reduce((result, category) => {
        // Check if the category itself matches the id
        if (category.id == id) {
            return category;
        }

        // // Check if any subcategory matches the id
        const subcategory = category.active_sub_categories.find(
            (sub) => sub.id == id
        );
        if (subcategory) {
            return subcategory;
        }

        // Continue checking the next categories or subcategories
        return result;
    }, null);
}

function getBundleById(id) {
    // Helper function to search bundles within a list
    const findBundleIn = (bundles) => bundles.find((bundle) => bundle.id == id);

    // Flatten categories and their subcategories into one array of bundles
    const allBundles = categories.flatMap((category) => [
        ...category.active_bundles, // Add the category bundles
        ...category.active_sub_categories.flatMap(
            (subcategory) => subcategory.active_bundles
        ), // Add subcategory bundles
    ]);

    // Use `find` to search through the flattened array of bundles
    return allBundles.find((bundle) => bundle.id == id) || null;
}

function bundleAddtionSelectToggle() {
    let additionBundleSelect = document.getElementById("additionBundleSelect");
    let BundleSelect = document.getElementById("bundleSelect");
    let bundleAddtionSelectOption = document.querySelector(
        "input[name='want_addition']:checked"
    );
    if (bundleAddtionSelectOption && bundleAddtionSelectOption.value === "1") {
        let bundle = getBundleById(BundleSelect.value);
        if (bundle?.addition_bundles?.length > 0) {
            let content = `<option selected hidden value="">اختر البرنامج الذي تريد دراسته في
                                        اكاديمية انس للفنون </option>`;

            bundle.addition_bundles.forEach((bundle) => {
                let isSelected =
                    bundle.id == oldValues?.addition_bundle_id
                        ? "selected"
                        : "";
                content += `<option value="${bundle.id}" ${isSelected}
                        has_certificate="${bundle.has_certificate}"
                        early_enroll="${bundle.early_enroll}"
                        >${bundle.title}
                        </option>`;
            });

            additionBundleSelect.innerHTML = content;
            additionBundleSelect.setAttribute("required", "required");
            additionBundleSelect.setAttribute("name", "addition_bundle_id");
            additionBundleSelect
                .closest("div.form-group")
                .classList.remove("d-none");
        } else {
            additionBundleSelect.innerHTML = `<option selected hidden value="">لا توجد برامج في هذا التصنيف</option>`;
            additionBundleSelect
                .closest("div.form-group")
                .classList.add("d-none");
            additionBundleSelect.removeAttribute("required");
            additionBundleSelect.removeAttribute("name");
        }
    } else {
        additionBundleSelect.closest("div.form-group").classList.add("d-none");
        additionBundleSelect.removeAttribute("required");
        additionBundleSelect.removeAttribute("name");
        resetSelect("additionBundleSelect");
    }
}

function coursesToggle() {
    let courseEndorsementInput = document.getElementById("course_endorsement");
    let courseEndorsementInput2 = document.getElementById(
        "course_endorsement2"
    );
    let courseEndorsementSection = courseEndorsementInput.closest("div");
    var courseSelect = document.getElementById("webinarSelect");
    if (courseSelect.selectedIndex == 1) {
        courseEndorsementSection.classList.remove("d-none");
        courseEndorsementInput.setAttribute("required", "required");
        courseEndorsementInput2.setAttribute("required", "required");
    } else {
        courseEndorsementSection.classList.add("d-none");
        courseEndorsementInput.removeAttribute("required");
        courseEndorsementInput2.removeAttribute("required");
    }
}

function CertificateSectionToggle() {
    let certificateSection = document.getElementById("certificate_section");
    let earlyEnroll = document.getElementById("early_enroll");
    let bundleSelect = document.getElementById("bundleSelect");
    let certificateInputs = document.querySelectorAll(
        "input[name='certificate']"
    );

    // Get the selected option
    var selectedOption = bundleSelect.options[bundleSelect.selectedIndex];
    if (selectedOption.getAttribute("has_certificate") == 1) {
        certificateSection.classList.remove("d-none");

        certificateInputs.forEach(function (element) {
            element.setAttribute("required", "required");
        });
    } else {
        certificateSection.classList.add("d-none");

        certificateInputs.forEach(function (element) {
            element.removeAttribute("required", "required");
        });
    }

    if (selectedOption.getAttribute("early_enroll") == 1) {
        earlyEnroll.classList.remove("d-none");
    } else {
        earlyEnroll.classList.add("d-none");
    }

    let RequirementEndorsementInput = document.getElementById(
        "requirement_endorsement"
    );
    let RequirementEndorsementSection =
        RequirementEndorsementInput.closest("div");
    if (bundleSelect.selectedIndex != 0) {
        RequirementEndorsementSection.classList.remove("d-none");
        RequirementEndorsementInput.setAttribute("required", "required");
    } else {
        RequirementEndorsementSection.classList.add("d-none");
        RequirementEndorsementInput.removeAttribute("required");
    }

    // let registerEndorsementInput = document.getElementById("register_endorsement");
    // let registerEndorsementSection = registerEndorsementInput.closest("div");
    // if (bundleSelect.selectedIndex != 0) {
    //     registerEndorsementSection.classList.remove("d-none");
    //     registerEndorsementInput.setAttribute("required", "required");
    // } else {
    //     registerEndorsementSection.classList.add("d-none");
    //     registerEndorsementInput.removeAttribute("required");
    // }
}

function showCertificateMessage(event) {
    let messageSection = document.getElementById("certificate_message");
    let certificateOption = document.querySelector(
        "input[name='certificate']:checked"
    );
    if (certificateOption.value === "1") {
        messageSection.innerHTML = "سوف تحصل على خصم 23%";
    } else if (certificateOption.value === "0") {
        messageSection.innerHTML = "بيفوتك الحصول على خصم 23%";
    } else {
        messageSection.innerHTML = "";
    }
}

function toggleHiddenInputs() {
    var select = document.getElementById("mySelect");
    var hiddenInput = document.getElementById("area");
    var hiddenLabel = document.getElementById("hiddenLabel");
    var hiddenInput2 = document.getElementById("city");
    var hiddenLabel2 = document.getElementById("hiddenLabel2");
    var cityLabel = document.getElementById("cityLabel");
    var town = document.getElementById("town");
    var anotherCountrySection = document.getElementById(
        "anotherCountrySection"
    );
    var region = document.getElementById("region");
    let anotherCountryOption = document.getElementById("anotherCountry");

    if (select && select.value !== "السعودية") {
        region.style.display = "block";
    } else {
        region.style.display = "none";
    }

    if (select.value === "اخرى") {
        anotherCountrySection.style.display = "block";
        anotherCountryOption.value = hiddenInput2.value;
    } else {
        anotherCountrySection.style.display = "none";
        anotherCountryOption.value = "اخرى";
    }
    if (select && cityLabel && town) {
        if (select.value === "السعودية") {
            town.outerHTML =
                '<select id="town" name="town"  class="form-control" required>' +
                '<option value="الرياض" selected="selected">الرياض</option>' +
                '<option value="جده">جده </option>' +
                '<option value="مكة المكرمة">مكة المكرمة</option>' +
                '<option value="المدينة المنورة">المدينة المنورة</option>' +
                '<option value="الدمام">الدمام</option>' +
                '<option value="الطائف">الطائف</option>' +
                '<option value="تبوك">تبوك</option>' +
                '<option value="الخرج">الخرج</option>' +
                '<option value="بريدة">بريدة</option>' +
                '<option value="خميس مشيط">خميس مشيط</option>' +
                '<option value="الهفوف">الهفوف</option>' +
                '<option value="المبرز">المبرز</option>' +
                '<option value="حفر الباطن">حفر الباطن</option>' +
                '<option value="حائل">حائل</option>' +
                '<option value="نجران">نجران</option>' +
                '<option value="الجبيل">الجبيل</option>' +
                '<option value="أبها">أبها</option>' +
                '<option value="ينبع">ينبع</option>' +
                '<option value="الخبر">الخبر</option>' +
                '<option value="عنيزة">عنيزة</option>' +
                '<option value="عرعر">عرعر</option>' +
                '<option value="سكاكا">سكاكا</option>' +
                '<option value="جازان">جازان</option>' +
                '<option value="القريات">القريات</option>' +
                '<option value="الظهران">الظهران</option>' +
                '<option value="القطيف">القطيف</option>' +
                '<option value="الباحة">الباحة</option>' +
                "</select>";
        } else {
            town.outerHTML = `<input type="text" id="town" name="town" placeholder="اكتب مدينه السكن الحاليه" class="form-control" value="${oldValues?.town}" >`;
        }
    }
}

function setCountry() {
    let anotherCountrySection = document.getElementById(
        "anotherCountrySection"
    );
    let anotherCountryOption = document.getElementById("anotherCountry");
    let another_country = document.getElementById("city");

    if (anotherCountrySection.style.display != "none") {
        // nationality.value = other_nationality.value;
        anotherCountryOption.value = another_country.value;
    }
}

function toggleNationality() {
    let other_nationality_section = document.getElementById(
        "other_nationality_section"
    );
    let nationality = document.getElementById("nationality");
    let other_nationality = document.getElementById("other_nationality");
    let anotherNationalityOption =
        document.getElementById("anotherNationality");
    if (nationality && nationality.value == "اخرى") {
        other_nationality_section.style.display = "block";

        // nationality.value = other_nationality.value;
        anotherNationalityOption.value = other_nationality.value;
    } else {
        other_nationality_section.style.display = "none";
        anotherNationalityOption.value = "اخرى";
    }
}

function setNationality() {
    let other_nationality_section = document.getElementById(
        "other_nationality_section"
    );
    let nationality = document.getElementById("nationality");
    let other_nationality = document.getElementById("other_nationality");
    let anotherNationalityOption =
        document.getElementById("anotherNationality");
    if (other_nationality_section.style.display != "none") {
        // nationality.value = other_nationality.value;
        anotherNationalityOption.value = other_nationality.value;
    }
}

// submit form
let form = document.getElementById("myForm");
let formButton = document.getElementById("form_button");
let directRegisterInput = document.getElementById("direct_register");
if (directRegisterInput) {
    directRegisterInput.value = "";
}

if (formButton) {
    formButton.onclick = function () {
        directRegisterInput.value = true;
        if (form.checkValidity()) {
            form.submit();
        } else {
            var invalidFields = form.querySelectorAll(":invalid");
            if (invalidFields.length > 0) {
                // Focus on the first invalid field
                invalidFields[0].focus();
                // Optionally scroll the field into view
                invalidFields[0].scrollIntoView({
                    behavior: "smooth",
                });
                invalidFields[0].reportValidity(); // Triggers the display of the built-in validation message
            }
        }
    };
}
