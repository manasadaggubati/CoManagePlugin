This plugin allows for an eligibility check to be performed as part of an
enrollment using attributes provided in the SAML assertion (via environment
variables).

**This plugin is for use with Registry v3.3.x only.** The interface for
Enrollment Flow Plugins changes with Registry v4.0.0, and this plugin will need
to be updated when upgrading.

Affiliation checks are based on _environment variables_. The desired affiliation
information must be made available by the web server via environment variables.
The check is performed after email verification, in order to ensure the desired
affiliation information is available.

Because of limitations in the Registry v3.x support for Enrollment Flow Plugins,
this plugin is configured at the _platform_ level, rather than the CO level. As
such, only a Platform Administrator (not a CO Administrator) can configure it.
After [installing and enabling](https://spaces.at.internet2.edu/display/COmanage/Installing+and+Enabling+Registry+Plugins)
the plugin, a new Platform menu item, _Affiliation Check Enrollers_, will become
available. Add new affiliation checks here, as follows:

* **Enrollment Flow**: Select the desired Enrollment Flow to perform the check
  during. All Enrollment Flows across all COs will be listed, so be sure to
  pick the correct flow.
* **Environment Variable Name**: The name of the environment variable to
  examine, without a leading `$`. eg: `SAML_AFFILIATION`.
* **Matching Regular Expression**: A regular expression, including leading and
  trailing slashes (`/`), to match against the contents of the variable. If the
  regular expression matches, the check is considered successful. eg:
  `/^faculty$/`, `/^nyu.*staff$/`.
* **Redirect URL**: If the check fails, a URL to send the enrollee to. This
  page should explain why the enrollment has been denied. If not specified, the
  enrollee will be sent to a generic URL with the message defined in the plugin
  text via the key `er.affiliationcheckenroller.msg`.

If enrollment is denied, the petition status will be changed to `Denied`.
Regardless, a record will be added to the Petition History indicating that a
check took place.

Multiple checks may be defined for the same flow. All checks must succeed for
the enrollment to be successful. The order of execution when multiple checks are
defined is not specified.