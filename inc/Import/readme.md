# Import API

Some quick ideas written down about the API.

## Common
aka IDontKnowWhereToPutTheseStuffNamespace 
Collects some really generic, stateless stuff like Pseudo-factories, sanitizing, etc.

## Controller

Controller assign listener (mostly Modules and Services) to the appropriate hooks. 

## Data

`ImportedTypeIdMapper`

Track ids of objects in the export system with the local ones to assign relations correctly. 
The Mapper listens to the actions from the types to get their ids (old and new) automatically.

## Iterator

Wraps around XMLReader to iterate over entity objects (terms, users, posts)

## ObjectCreation

Deprecated and will be removed. It's planned to use a DI container for that.

## Service

Services defines the first »logic level«. A service performs a concrete, well defined task such as 
parsing a term or importing post.

## Types

`ImportPostInterface`, `ImportTerm`, etc.

To have a clear defined interface to the importing data. Types are almost immutable. The only value that can changed 
once in the object live time is the id of the object, as this gets created when inserting it into DB. This change will 
trigger an action so that a mapper can »observe« these objects independently.