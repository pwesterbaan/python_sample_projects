class connected_component:
    __slots__ = '_vertices','_outgoingEdges','_interiorEdges'

    def __init__(self, vertices, outgoingEdges, interiorEdges=[]):
        """
        Create a connected_component with list of the vertices,
        set of outgoing edges, and set of interior edges
        """
        self._vertices=[vertices]
        self._outgoingEdges=set(outgoingEdges)
        self._interiorEdges=set(interiorEdges)

    def __hash__(self):
        return hash(id(self))
  
    def __str__(self):
        return str((self._vertices, self._outgoingEdges, self._interiorEdges))

    def size(self):
        return len(self._vertices)
    
    """
    Append a vertex to this connected component
    """
    def add_vertex(self, vertex):
        self._vertices.append(vertex)

    """
    Add an outgoing edge to this connected component
    """
    def add_outgoingEdge(self, edge):
        self._outgoingEdges.add(edge)

    """
    Add an interior edge to this connected component
    A connected component has n-1 interior edges
    """
    def add_interiorEdge(self, edge):
        self._interiorEdges.add(edge)

    """
    generator for all outgoing edges
    """
    def vertices(self):
        for vertex in self._vertices:
            yield vertex

    """
    generator for all outgoing edges
    """
    def outgoing_edges(self):
        for edge in self._outgoingEdges:
            yield edge

    """
    generator for all interior edges
    """
    def interior_edges(self):
        for edge in self._interiorEdges:
            yield edge