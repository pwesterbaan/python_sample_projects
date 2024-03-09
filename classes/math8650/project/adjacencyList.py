#!/usr/bin/env python2
# -*- coding: utf-8 -*-
"""
Created on Thu Nov 15 12:19:37 2018

@author: peter
"""
from __future__ import print_function
import csv


inputCsv='input.csv'
def createEdgeDict(inputCsv):
    """Function that takes a CSV file of edges 
       and returns a dictionary of edges"""
    with open(inputCsv) as csvFile:
        csv_reader = csv.reader(csvFile, delimiter=",")
        adjacency_map={};
        for row in csv_reader:
            if row[0] not in adjacency_map:
                adjacency_map[row[0]]=[]
                for ind,val in enumerate(row[1:]):
                    # Don't store blank space or duplicates
                    if val!='' and (val not in adjacency_map[row[0]]):
                        if val in adjacency_map and row[0] in adjacency_map[val]:
                            continue
                        adjacency_map[row[0]].append(val)
                        ### Earlier version included reflexive relationship
                        #                if val not in adjacency_map:
                        #                    adjacency_map[val]=[]
                        #                # Edges are a reflexive relation
                        #                adjacency_map[val].append(row[0])
    return adjacency_map

adjacency_map=createEdgeDict(inputCsv)

print(adjacency_map)