# Bit-flag-example
A class using an implementation of bit flags and bitwise operators

This class allows a user to register a prize for each day during a given period. Rather than enter each entry separately into a database 
field (or use something crude like a comma-delimited list in a field), this class creates a bit flag for each day. The bit flags are then
used to create a final entry to be entered as a single integer entry into a db table.

The bit flags themsevles are declared in the $day_flags static property. Once we have the day, we get the flag, and then update the 
day_flags field in the db with the syntax:  "SET day_flags = day_flags | {$day_flag}".

To determine whether a flag exists using MySQL, the syntax is "WHERE day_flag & {$day_flag}"
