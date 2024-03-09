import numpy as np
import numpy.linalg as la

#scalar version
def f(x):
    #x^2+y^2-4 with xy=1
    return(x**2+1/(x**2)-4)

def fp(x):
    #2x-2x^-3
    return(2*x-2/x**3)

#vector version
def fv(x):
    #|x^2+y^2-4|
    #|   xy-1  |
    return(np.array([x[0]**2+x[1]**2-4,x[0]*x[1]-1]))

def fpv(x):
    #|2x  2y|
    #| y   x|
    return(np.matrix([[2*x[0],2*x[1]],[x[1],x[0]]]))

tol=1e-12

x=2                   #guessing initial scalar value of 2
vx=np.array([2,0.5])  #guessing initial vector value of [2,1/2]

for ind in range(10):
    x_new=x-f(x)/fp(x)
    if abs(x_new-x)<tol:
        x=x_new
        break
    x=x_new
print(f"""scalar version: converged in {ind} steps""")
print(f"""  x={x}, y={1/x}""")

for ind in range(10):
    vx_new=vx-la.solve(fpv(vx),fv(vx))
    if la.norm(vx_new-vx)<tol:
        vx=vx_new
        break
    vx=vx_new
print(f"""vector version: converged in {ind} steps""")
print(f"""  x={vx[0]}, y={vx[1]}""")
