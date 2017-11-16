var Grid_Direction = Object.freeze({
   None:    {value: 0, name: "None",  code: " "}, 
   North:   {value: 1, name: "North", code: "N"}, 
   East:    {value: 2, name: "East",  code: "E"}, 
   South:   {value: 3, name: "South", code: "S"}, 
   West:    {value: 4, name: "West",  code: "W"}
});

function Grid_DirectionByName(name)
{
   if (name == Grid_Direction.North.name)
      return Grid_Direction.North;
   if (name == Grid_Direction.East.name)
      return Grid_Direction.East;
   if (name == Grid_Direction.South.name)
      return Grid_Direction.South;
   if (name == Grid_Direction.West.name)
      return Grid_Direction.West;
   return cell;
}
function Grid_DirectionByCode(code)
{
   if (code == Grid_Direction.North.code)
      return Grid_Direction.North;
   if (code == Grid_Direction.East.code)
      return Grid_Direction.East;
   if (code == Grid_Direction.South.code)
      return Grid_Direction.South;
   if (code == Grid_Direction.West.code)
      return Grid_Direction.West;
   return cell;
}
function Grid_ReverseDirection(gridDirection)
{
   if (gridDirection == Grid_Direction.North)
      return Grid_Direction.South;
   if (gridDirection == Grid_Direction.East)
      return Grid_Direction.West;
   if (gridDirection == Grid_Direction.South)
      return Grid_Direction.North;
   if (gridDirection == Grid_Direction.West)
      return Grid_Direction.East;
   assert(0);
}
function Grid_IsVerticalTableEdge(cell)
{
   var table = Grid_GetCellTable(cell);
   var iCol = Grid_GetCellColumn(cell);
   return iCol == 0 || iCol == Grid_GetTableColumnCount(table)-1;
}
function Grid_IsHorizontalTableEdge(cell)
{
   var table = Grid_GetCellTable(cell);
   var iRow = Grid_GetCellRow(cell);
   return iRow == 0 || iRow == Grid_GetTableRowCount(table)-1;
}
function Grid_IsTableEdge(cell)
{
   var table = Grid_GetCellTable(cell);
   var iCol = Grid_GetCellColumn(cell);
   var iRow = Grid_GetCellRow(cell);
   return iCol == 0 || iCol == Grid_GetTableColumnCount(table)-1 || 
          iRow == 0 || iRow == Grid_GetTableRowCount(table)-1;
}
function Grid_IsWestTableEdge(cell)
{
   var iCol = Grid_GetCellColumn(cell);
   return iCol == 0;
}
function Grid_IsEastTableEdge(cell)
{
   var table = Grid_GetCellTable(cell);
   var iCol = Grid_GetCellColumn(cell);
   return iCol == Grid_GetTableColumnCount(table)-1;
}
function Grid_IsNorthTableEdge(cell)
{
   var iRow = Grid_GetCellRow(cell);
   return iRow == 0;
}
function Grid_IsSouthTableEdge(cell)
{
   var table = Grid_GetCellTable(cell);
   var iRow = Grid_GetCellRow(cell);
   return iRow == Grid_GetTableRowCount(table)-1;
}
function Grid_InsertRowNorth(cell,initFunc/*=null*/,count/*=1*/)
{
   assert(cell != null);
   assert(cell.parentNode != null);
   
   var table = cell.parentNode.parentNode.parentNode;
   var iRow = cell.parentNode.rowIndex;
   var cols = table.rows[0].cells.length;
   
   if (arguments.length < 3)
      count = 1;
   
   while (count-- > 0)
   {
      // create an empty <tr> element and add it to the table
      var row = table.insertRow(iRow++);

      // insert new cells (<td> elements) to the new <tr> element
      for (var iCol = 0; iCol < cols; iCol++)
      {
         cell = row.insertCell(iCol);
         if (arguments.length >= 2)
            initFunc(cell);
      }
   }
}
function Grid_InsertRowSouth(cell,initFunc/*=null*/,count/*=1*/)
{
   assert(cell != null);
   assert(cell.parentNode != null);

   var table = cell.parentNode.parentNode.parentNode;
   var iRow = cell.parentNode.rowIndex+1;
   var cols = table.rows[0].cells.length;
   
   if (arguments.length < 3)
      count = 1;
   
   while (count-- > 0)
   {
      // create an empty <tr> element and add it to the table
      var row = table.insertRow(iRow++);

      // insert new cells (<td> elements) to the new <tr> element
      for (var iCol = 0; iCol < cols; iCol++)
      {
         cell = row.insertCell(iCol);
         if (arguments.length >= 2)
            initFunc(cell);
      }
   }
}
function Grid_RemoveRow(cell)
{
   assert(cell != null);
   assert(cell.parentNode != null);
   
   var table = cell.parentNode.parentNode.parentNode;
   var iRow = cell.parentNode.rowIndex;
   table.deleteRow(iRow);
}
function Grid_InsertColumnWest(cell,initFunc/*=null*/,count/*=1*/)
{
   assert(cell != null);
   assert(cell.parentNode != null);
   
   var table = cell.parentNode.parentNode.parentNode;
   var iCol = cell.cellIndex;

   if (arguments.length < 3)
      count = 1;
   
   while (count-- > 0)
   {
      // insert a new cell (<td> element) into each row (<tr> element)
      for (iRow = 0; iRow < table.rows.length; iRow++)
      {
         cell = table.rows[iRow].insertCell(iCol);
         if (arguments.length >= 2)
            initFunc(cell);
      }
      iCol++;
   }
}
function Grid_InsertColumnEast(cell,initFunc/*=null*/,count/*=1*/)
{
   assert(cell != null);
   assert(cell.parentNode != null);
   
   var table = cell.parentNode.parentNode.parentNode;
   var iCol = cell.cellIndex+1;

   if (arguments.length < 3)
      count = 1;
   
   while (count-- > 0)
   {
      // insert a new cell (<td> element) into each row (<tr> element)
      for (iRow = 0; iRow < table.rows.length; iRow++)
      {
         cell = table.rows[iRow].insertCell(iCol);
         if (arguments.length >= 2)
            initFunc(cell);
      }
      iCol++;
   }
}
function Grid_RemoveColumn(cell)
{
   assert(cell != null);
   assert(cell.parentNode != null);
   
   var table = cell.parentNode.parentNode.parentNode;
   var iCol = cell.cellIndex;
   
   // remove a cell (<td> element) from each row (<tr> element)
   for (iRow = 0; iRow < table.rows.length; iRow++)
   {
      cell = table.rows[iRow].deleteCell(iCol);
   }
}
function Grid_GetCellByCoord(table, iRow, iCol)
{
   assert(table != null);
   if (iRow < 0 || iRow >= table.rows.length) return null;
   if (iCol < 0 || iCol >= table.rows[iRow].cells.length) return null;   
   return table.rows[iRow].cells[iCol];
}
function Grid_GetCellRow(cell)
{
   assert(cell != null);
   return cell.parentNode.rowIndex;
}
function Grid_GetCellColumn(cell)
{
   assert(cell != null);
   return cell.cellIndex;
}
function Grid_GetCellTable(cell)
{
   assert(cell != null);
   return cell.parentNode.parentNode.parentNode;
}
function Grid_GetTableRowCount(table)
{
   assert(table != null);
   return table.rows.length;
}
function Grid_GetTableColumnCount(table)
{
   assert(table != null);
   return table.rows[0].cells.length;
}
function Grid_GetCellNorth(cell)
{
   var table = Grid_GetCellTable(cell);
   var iCol = cell.cellIndex;
   var iRow = cell.parentNode.rowIndex-1;
   return Grid_GetCellByCoord(table, iRow, iCol);
}
function Grid_GetCellSouth(cell)
{
   var table = Grid_GetCellTable(cell);
   var iCol = cell.cellIndex;
   var iRow = cell.parentNode.rowIndex+1;
   return Grid_GetCellByCoord(table, iRow, iCol);
}
function Grid_GetCellWest(cell)
{
   var table = Grid_GetCellTable(cell);
   var iCol = cell.cellIndex-1;
   var iRow = cell.parentNode.rowIndex;
   return Grid_GetCellByCoord(table, iRow, iCol);
}
function Grid_GetCellEast(cell)
{
   var table = Grid_GetCellTable(cell);
   var iCol = cell.cellIndex+1;
   var iRow = cell.parentNode.rowIndex;
   return Grid_GetCellByCoord(table, iRow, iCol);
}
function Grid_GetCellByDirection(cell,direction)
{
   if (direction == Grid_Direction.North)
      return Grid_GetCellNorth(cell);
   if (direction == Grid_Direction.East)
      return Grid_GetCellEast(cell);
   if (direction == Grid_Direction.South)
      return Grid_GetCellSouth(cell);
   if (direction == Grid_Direction.West)
      return Grid_GetCellWest(cell);
   return cell;
}
function Grid_InsertByDirection(cell,direction,initFunc/*=null*/)
{
   if (direction == Grid_Direction.North)
      Grid_InsertRowNorth(cell, initFunc);
   else if (direction == Grid_Direction.East)
      Grid_InsertColumnEast(cell, initFunc);
   else if (direction == Grid_Direction.South)
      Grid_InsertRowSouth(cell, initFunc);
   else if (direction == Grid_Direction.West)
      Grid_InsertColumnWest(cell, initFunc);
}
