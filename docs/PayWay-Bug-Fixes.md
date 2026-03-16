# Bugs fixes

- Sometimes when a user would log in with the correct login details, they wouldn’t be routed to the dashboard page.
    - API post request to the backend would go through correctly and perform expectedly
    - FIX - Added in “type = “button” to the login button so the browser would treat the login button as a button and not a submit as it’s included within a form tag