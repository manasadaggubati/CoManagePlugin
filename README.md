# CoManagePlugin

1. The ResearchNavigatorProvisioner plugin writes provisioning data to a MySQL
   table in accordance with the format agreed upon.
2. The IdentifierHasher plugin generates shortened identifiers, primarily
   intended for use with the ResearchNavigatorProvisioner. See the README
   in the plugin directory for more details.
3. The AffiliationCheckerEnroller checks to see if an enrollee has an acceptable
   affiliation before completing enrollment. See the README in the plugin
   directory for more details.