document.addEventListener("DOMContentLoaded", function () {
   const input = document.querySelector("#contact_number, #billing_phone, #completion_phone_number");
   const errorMsg = document.querySelector("#phone-error");
   const form = input.closest("form");

   const iti = window.intlTelInput(input, {
      initialCountry: "auto",
      nationalMode: false,
      geoIpLookup: (callback) => {
         fetch("https://ipapi.co/json")
            .then((res) => res.json())
            .then((data) => callback(data.country_code))
            .catch(() => callback("US"));
      },
      loadUtils: () => import("https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.11/build/js/utils.js"),
   });

   const errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

   function validatePhone() {
      if (input.value.trim() === "") {
         errorMsg.textContent = "";
         return true;
      }

      if (iti.isValidNumber()) {
         errorMsg.textContent = "";
         // input.classList.remove("is-invalid");
         return true;
      }

      const errorCode = iti.getValidationError();
      errorMsg.textContent = errorMap[errorCode] || "Invalid phone number";
      // input.classList.add("is-invalid");

      return false;
   }

   // Validate while typing
   input.addEventListener("input", validatePhone);
   input.addEventListener("countrychange", validatePhone);

   // Validate on submit
   form.addEventListener("submit", async (e) => {
      await iti.promise;

      if (!validatePhone()) {
         e.preventDefault();
         input.focus();
         return;
      }

      // Convert to international format
      input.value = iti.getNumber();
   });
});
