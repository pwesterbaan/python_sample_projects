import graph as graph
import semiring as sr
import numpy as np

# spgen problem-type n_nodes n_arcs cost_min cost_max [% high-cost [seed]]
#./spgen2 sp 7 10 0 5 25 | tee sp_output_data


#read in file and do the stuff
with open("sp_output_data","r") as file:
    for e in file:
        current_line=e.split(" ")
        if 'c' == current_line[0]:
            print(" ".join(current_line[1:]).strip("\n"))
        if 'p' == current_line[0]:
            probType=current_line[1]
            nn=int(current_line[2])
            mm=int(current_line[3])
            if probType == 'sp':
                sr_obj=sr.shortest_path(nn)
            elif probType == 'mr':
                sr_obj=sr.most_reliable(nn)
            elif probType == 'mc':
                sr_obj=sr.max_cap(nn)
            elif probType == 'tc':
                sr_obj=sr.trans_closure(nn)
            arcs=sr_obj.add_unit()*np.ones((nn,nn))
        if 'a' == current_line[0]:
            i=int(current_line[1])
            j=int(current_line[2])
            val=int(current_line[3])
            sr_obj.set_arc(i,j,val)

for ind in range(nn):
    weightMat[ind][ind]=algStruct.mult_unit()
for aij in arcs:
    print(aij)
print('\n')
for weightList in weightMat:
    print(weightList)
print('\n')
for path in pathMat:
    print(path)
