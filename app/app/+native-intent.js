// Link rules for links that open the app from outside. The browser comes back from "Continue with Google" through lango://auth?code=…
// (see lib/google.js). That is not a screen: lib/google.js reads the code itself, so the router must leave the person where they are
// (otherwise it shows "Unmatched route"). Every other link goes to the router as it is.

// lango://auth?code=…, /auth?code=… and (Expo Go) exp://host:8081/--/auth?code=… all end up as "auth?…" here.
const isGoogleReturn = (path) => {
  const rest = String(path)
    .replace(/^[a-z][a-z0-9+.-]*:\/\//i, '')
    .replace(/^.*?\/--\//, '')
    .replace(/^\/+/, '');
  return /^auth(?:[/?#]|$)/.test(rest);
};

export function redirectSystemPath({ path }) {
  try {
    return isGoogleReturn(path) ? null : path;
  } catch {
    return path;
  }
}
