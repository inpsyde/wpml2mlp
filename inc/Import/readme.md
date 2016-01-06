# Import API

Some quick ideas written down about the API.

## Iterator

Wraps around XMLReader to iterate over entity objects (terms, users, posts)

## Types 

`ImportPostInterface`, `ImportTerm`, etc.

To have a clear defined interface to the importing data. Types are almost immutable. The only value that can changed 
once in the object live time is the id of the object, as this gets created when inserting it into DB. This change will 
trigger an action so that a mapper can »observe« these objects independently.

## Data

`ImportedTypeIdMapper`

Track ids of objects in the export system with the local ones to assign relations correctly. 
The Mapper listens to the actions from the types to get their ids (old and new) automatically.

## Controller

Assign the DataMapper to the actions of the types …