# UF-sprinkle-selftermination
This UserFrosting sprinkle does two things: 
1) Redacts basic user [personal data](https://en.wikipedia.org/wiki/Personal_data) when deleting an account 
2) Provides a button for each user to delete their own account.

# Install directions
* Include `SelfTermination::class` in your Sprinkle Recipe:
```php
  use SelfTermination\Sprinkle\SelfTermination;
  ...
  public function getSprinkles(): array
  {
      return [
//        Core::class,
//        Account::class,
//        Admin::class,
//        AdminLTE::class,
		        SelfTermination::class,
        ];
    }
```
* Include `strykeslammerii/selftermination-sprinkle` in `composer.json`
```json
    "require": {
        ...
	  "strykeslammerii/selftermination-sprinkle": "^dev-main"
    },
```
* Include `strykeslammerii/selftermination-sprinkle` in `package.json`
```json
    "dependencies": {
        "@userfrosting/sprinkle-admin": "^5.0",
        "@userfrosting/theme-adminlte": "^5.0",
	       "@strykeslammerii/selftermination-sprinkle": "^dev-main"
    },
```
* Install `` seed
** First, in Bakery
```txt
$ php bakery seed
Seeder
======
 Select seed(s) to run. Multiple seeds can be selected using comma separated values:
  [0] UserFrosting\Sprinkle\Account\Database\Seeds\DefaultGroups
  [1] UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions
  [2] UserFrosting\Sprinkle\Account\Database\Seeds\DefaultRoles
  [3] SelfTermination\Sprinkle\Database\Seeds\SelfTerminationPermission
 > 3
```
** Then, add to appropriate roles through UF's user management UI

# In more detail
## Redacts Personal Data
Base UF only soft-deletes users. This allows for users to be un-deleted, and leaves their personal data in the user database.
* This sprinkle overrides the `delete('/u/{user_name}')` route to [redact](https://en.wikipedia.org/wiki/Sanitization_(classified_information)) a user's email (to `"redacted".$user->id."@test.com"`), username (to `"redacted".$user->id`), and first and last name (both to `"redacted"`) in the user database.
  * The redacted email and username retain the user id number, in case of questions about the deletion. Discussion about this lack of redaction would be appreciated.
* The hashed user password is also changed to a two-character entry using `bin2hex(random_bytes(1)`, which should be too short to match any password the application checks for login. 

* The activity log is noted with the redacted username; either "redacted15 self-deleted" or "User Admin_Alice deleted the account for redacted15."
  * Existing activity logs are NOT redacted. They are currently stored completely as strings with the user data *embedded*, rather than *referencing* the users they refer to.
  *  A search-and-replace or complete refactoring of the activity log system were both beyond the scope of this sprinkle. Pull requests which address this point are welcome.
  *  **Users of this sprinkle should be aware of how user personal data is stored elsewhere in their systems.** This sprinkle can be extended to fit other use cases, but does not on its own search out and redact personal data that may be generated or stored by other sprinkles.
     *  Specifically, we mention above two ways that personal data can be stored: "references" and "embedded". A sprinkle generating its own embedded personal data will need to do extra work to redact that.
     *  If user content is allowed, it is very common for users to mention each other by name. `@`mentions may already be pulled out and used as a reference to a user, but nicknames or shortened forms would be treated as any other text.
Using myself as an example: `@StrykeSlammerII` is obviously me. Even a search-and-redact would find those easily. `Strike`, however, would not be a valid `@`mention and is a "normal" word that should **not** be redacted throughout all user comments!
* A soft delete is then performed as usual. However, un-deletion cannot revert the redactions, and a new password would need to be set before the "restored" account could be used again. 
* It is theoretically possible than a deleted account could still be stolen by a third party: they would need access to either an admin user or the User database. 
If this sprinkle is used in an environment where users can interact, it is important to be *certain* to redact connections and interactions between users... and to consider using hard deletes instead of soft ones.

## Self-deletion
This button requires the `Self-termination` permission (included) to be added to the user's role (which this sprinkle does NOT attempt to do!)
The button is located at the bottom of the "My account" page. It looks less like a button and more like a link--but it just brings up an "Are you sure?" modal with proper yes/no buttons, similar to the way the Admin "delete user" modal works.

# Moving forward
This being my second sprinkle ever, I'm sure there are plenty of bad practices and inefficiencies. It doesn't even have any unit or integration tests! Manual testing made me think basic functionality works. I'm using it in my main project, so if I find issues I will patch them, but I don't have plans to work on this sprinkle just to work on it.

Pull requests are appreciated, though other projects may prefer to fork and extend this if additional redactions need to be made.

## Localization
This is mostly a personal project and I'm monolingual... so any messaging I've added will not be localized.
Again, pull requests are appreciated!
