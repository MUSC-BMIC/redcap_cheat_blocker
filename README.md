# Cheat Blocker Plugin (REDCap External Module)

## Configuration

- Enable the module in your project, if it is not already enabled
- Click **Configure** for **CheatBlocker**

Duplicate Check Indicator:  a variable that indicates whether a participant is a duplicate based on rules configured for the study
Failed Criteria: a variable that indicates which rule failed
Duplicate Record IDs: a variable that lists all the record ids that are considered as duplicates and they are separated by commas


There are 2 methods of creating variables within the REDCap project - using the Online Designer OR by uploading data dictionary

**Method #1 - Using Online Designer**

All the variables have to be manually created by navigating to the Designer Page.


**Setting up the Duplicate Check Indicator (required):**
- In your project, navigate to the ‘Designer Page’
- Click to Modify the Instrument
- Click ‘Add Field’
- **Within the ‘Add New Field’ modal:**
  - ‘Field Type’ should be set to ‘Yes - No'
  - Under ‘Action Tags / Field Annotation’ add the tag @hidden-survey
  - Under ‘Variable Name’, name your "Duplicate Check" variable and Save


**Setting up the Failed Criteria variable (required):**
- In your project, navigate to the ‘Designer Page’
- Click to Modify the Instrument
- Click ‘Add Field’
- **Within the ‘Add New Field’ modal:**
  - ‘Field Type’ should be set to ‘Text Box (Short Text, Number, Date/Time, ...)'
  - Under ‘Action Tags / Field Annotation’ add the tag @hidden-survey
  - Under ‘Variable Name’, name your "Failed Criteria" variable and Save


**Setting up the Duplicate Record IDs variable (required):**
- In your project, navigate to the ‘Designer Page’
- Click to Modify the Instrument
- Click ‘Add Field’
- **Within the ‘Add New Field’ modal:**
  - ‘Field Type’ should be set to ‘Text Box (Short Text, Number, Date/Time, ...)'
  - Under ‘Action Tags / Field Annotation’ add the tag @hidden-survey
  - Under ‘Variable Name’, name your "Duplicate Record IDs' variable and Save


**Setting up the Data Entry Time variable (required):**
- In your project, navigate to the ‘Designer Page’
- Click to Modify the Instrument
- Click ‘Add Field’
- **Within the ‘Add New Field’ modal:**
  - ‘Field Type’ should be set to ‘Text Box (Short Text, Number, Date/Time, ...)'
  - Under ‘Action Tags / Field Annotation’ add the tag @hidden-survey
  - Under ‘Variable Name’, name your "Data Entry Time' variable and Save



**Method #2 - Uploading Data Dictionary**

The Data Dictionary is a more advanced method which allows to view/edit all the variables in a single csv file.
Navigate to Data Dictionary and upload the data dictionary in the link shown below.

[Data Dictionary link](https://github.com/HSSC/redcap_cheat_blocker/blob/master/CheatBlocker_data_dictionary.csv)

Upload the data dictionary and then commit changes after it is uploaded. The new Data Dictionary will completely overwrite your existing variables, so you want to be sure you've uploaded the right file.

- Navigate to ‘Applications >> External Modules’ and click to configure CheatBlocker.

If both Quota Config & Cheat Blocker modules are enabled, upload the combined data dictionary in the link shown below

[Data Dictionary link](https://github.com/HSSC/redcap_quotas/blob/master/QuotaConfig_CheatBlocker_data_dictionary.csv)



