# spgen problem-type n_nodes n_arcs cost_min cost_max [% high-cost [seed]]
#./spgen2 sp 7 10 0 5 25 | tee sp_output_data
import graph as graph
import semiring as sr
import numpy as np

sr=sr.semiring()

#read in file and do the stuff
with open("sp_output2_data","r") as file:
    for e in file:
        current_line=e.split(" ")
        # while current_line[0] != 'p'
        #   read lines, then do stuff based on problem type
        if 'c' == current_line[0]:
            # do nothing (delete this later)
            # print(f'Comment: {current_line}')
            print(" ".join(current_line[1:]).strip("\n"))
            pass
        if 'p' == current_line[0]:
            probType=current_line[1]
            nn=int(current_line[2])
            mm=int(current_line[3])
            algStruct=sr.make_semiring(probType)
            arcs=algStruct.add_unit()*np.ones((nn,nn))
            weightMat=algStruct.add_unit()*np.ones((nn,nn))
            pathMat=[[None]*nn for _ in range(nn)]
        if 'n' == current_line[0]:
#             ind=int(current_line[1])
#             val=int(current_line[2])
#             nodes[ind]=val
            pass
        if 'a' == current_line[0]:
            i=int(current_line[1])
            j=int(current_line[2])
            val=int(current_line[3])
            arcs[i][j]=val

for aij in arcs:
    print(aij)
print('\n')
for ind in range(nn):
    weightMat[ind][ind]=algStruct.mult_unit()
for weightList in weightMat:
    print(weightList)
print('\n')
for path in pathMat:
    print(path)

################################################################################

import itertools

for ind in range(nn):
    for tail in range(nn):
        for head in itertools.chain(range(tail),range(tail+1,nn)):
            label_w=weightMat[ind][head]
            label_v=weightMat[ind][tail]
            a_vw=arcs[tail][head]
            intermediate_label=algStruct.otimes(a_vw,label_v)
            tmp_label_w=algStruct.oplus(label_w,intermediate_label)
            if label_w> tmp_label_w:
                weightMat[ind][head]=tmp_label_w
                pathMat[tail][head]=tail

for weightList in weightMat:
    print(weightList)
print('\n')
for path in pathMat:
    print(path)
