#!/usr/bin/env python2
# -*- coding: utf-8 -*-
"""
Created on Fri Nov  9 08:31:11 2018

LAST UPDATED: Todd Morra 11/18/18 @4:01pm

@author: toddmorra
"""
    
class system():
    """"The is the power system class. Embedded are the bus and line classes."""
    
    __slots__ = '_line_dict'
    
    class bus():
    
        __slots__ = '_label','_observed','_lines_unobserved','_has_pmu','_adjacency_list'
        
        def __init__(self,label):
            self._label = str(label)
            self._observed = False
            self._lines_unobserved = 0
            self._has_pmu = False
            self._adjacency_list = []
    
        def place_pmu(self):
            self._has_pmu = True
            #needs to call another function to ripple outward in the system
        
        def is_observed(self):
            return self._observed
        
        def is_saturated(self):
            return self._lines_unobserved == 0
        
        def __eq__(self):
            #how do we want to determine if two buses are equal?
            pass
        
        def __hash__(self):
            """This allows us to use bus instances as keys in a dictionary."""
            return hash(id(self))
        

    class line():
        """This the power line class. The standard ._element trait has been
        replaced with ._observed."""
        __slots__ = '_head','_tail','_observed'
        
        def __init__(self,bus1,bus2):
            self._tail = bus1
            self._head = bus2
            self._observed = False
            
        def is_observed(self):
            return self._observed
        
        def opposite(self,bus):
            a,b = self._origin,self._destination
            if a == bus:
                return b
            elif b ==  bus:
                return a
            else:
                print("This line is not incident to the given bus.")
                pass
        
        def endpoints(self):
            return (self._origin,self._destination)
    
    def __init__(self):
        """
        This _line_dict is the core of the system and will be a dictionary of dictionaries.
        Each bus is a key in _line_dict and the value is a secondary dictionary.
        Keys in the secondary dictionary are buses incident to the first.
        Values in the secondary dictionary are lines.
        
        self._lint_dict[u][v] = e means edge e lives between bus u and v.
        """
        self._line_dict = {}
    
    def bus_count(self):
        """Returns the number of buses in the system."""
        return len(self._line_dict)
    
    def buses(self):
        """Returns an iteration of the buses of the system."""
        return self._line_dict.keys()
    
    def bus_labels(self):
        """Returns an iteration of the labels of the buses in the system."""
        for bus in self._line_dict.keys():
            print bus._label+',',
    
    def line_count(self):
        """Returns the number of lines in the system."""
        return sum(len(self._line_dict[bus]) for bus in self._line_dict())//2
    
    def lines(self):
        """Returns an iteration of the lines in the system."""
        result = set()
        for secondary_map in self._line_dict:
            result.update(secondary_map.values())
        return result
    
    def get_line(self,a,b):
        """ Returns the line between a and b. Returns nothing if not adjacent"""
        self._line_dict[a].get(b)
        
    def make_bus(self,label):
        b = self.bus(label)
        self._line_dict[b]={}
        return b
    
    def get_bus(self,label):
        """Returns the bus instance labeled with the given label,
        creating a new bus if necessary."""
        for bus in self.buses():
            if bus._label == label:
                return bus
        return self.make_bus(label)
    
    def degree(self,a):
        """Returns the number of lines incident to bus a."""
        return len(self._line_dict[a])
    
    def incident_lines(self,bus):
        """Returns every line incident to the given bus."""
        for line in self._line_dict[bus].values():
            yield line
    
    def make_line(self,label1,label2):
        """Make a line between the buses associated to the two given labels."""
        print("I'm making a pair of buses.")
        bus1,bus2=self.get_bus(label1),self.get_bus(label2)
        l = self.line(bus1,bus2)
        self._line_dict[bus1][bus2]=l
        self._line_dict[bus2][bus1]=l
        bus1._adjacency_list.append(bus2)
        bus2._adjacency_list.append(bus1)
    
def make_system(edge_dict):
    """Takes a dictionary with key:value = bus:adjacent_bus. If a:b appears
    in the dictionary, then b:a will not appear, even though the system
    is undirected."""
    new_system = system()
    
    for bus_label in edge_dict.keys():
        for adjacent_label in edge_dict[bus_label]:
            print("I'm making a line")
            new_system.make_line(bus_label,adjacent_label)
    
    return new_system
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    